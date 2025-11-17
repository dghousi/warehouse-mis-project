<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class LocaleGenerator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public const SUPPORTED_LOCALES = ['en', 'dr', 'ps'];

    public const INDENT_SEPARATOR = ",\n  ";

    public const LINE_SEPARATOR = ",\n      ";

    public function generate(string $module, string $entity, array $fields, Command $command, Logger $logger): void
    {
        $moduleDir = Str::kebab($module);
        $kebabEntity = Str::kebab($entity);

        $localeStub = File::get(base_path('stubs/frontend/locale.stub'));

        $fieldEntries = array_map($this->generateLocaleField(...), $fields);
        $fieldsJson = $fieldEntries !== [] ? implode(self::INDENT_SEPARATOR, $fieldEntries) : '';
        $tableJson = $this->generateTableTranslations($entity);
        $repoJson = $this->generateRepositoryTranslations($entity);
        $presentationJson = $this->generatePresentationTranslations($entity);

        $localeContent = str_replace(
            ['{fields}', '{table}', '{repository}', '{entity}', '{presentation}'],
            [$fieldsJson, $tableJson, $repoJson, $entity, $presentationJson],
            $localeStub
        );

        $logger->info("Generated locale content:\n".$localeContent, $command);

        $parsedContent = json_decode($localeContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $logger->error('Invalid JSON in locale stub: '.json_last_error_msg(), $command);
            throw new Exception('Invalid JSON in locale stub'); // NOSONAR
        }

        $prettyJson = json_encode($parsedContent, JSON_PRETTY_PRINT);
        $formattedContent = preg_replace_callback(
            '/^((?: {4})+)/m',
            fn ($matches) => str_repeat('  ', strlen($matches[1]) / 4),
            $prettyJson
        );

        $logger->info("Formatted JSON content:\n".$formattedContent, $command);

        foreach (self::SUPPORTED_LOCALES as $locale) {
            $localePath = base_path("../frontend/src/messages/{$locale}/{$moduleDir}/{$kebabEntity}.json");
            File::ensureDirectoryExists(dirname($localePath));
            File::put($localePath, $formattedContent);
            $logger->info("Generated locale: {$localePath}", $command);
        }
    }

    public function generateLocaleField(array $field): string
    {
        $name = $field['name'];
        $label = implode(' ', array_map(ucfirst(...), preg_split('/(?=[A-Z])|_/', (string) $name)));

        $validations = [];
        $validationConfigs = config('frontend_generator.validations', [
            'required' => [
                'condition' => fn ($f) => $f['required'],
                'rule' => 'required: "config.form.{name}.validation.required"',
            ],
            'email' => [
                'condition' => fn ($f): bool => $f['name'] === 'email',
                'rule' => 'pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "config.form.{name}.validation.email" }',
            ],
            'password' => [
                'condition' => fn ($f): bool => $f['name'] === 'password',
                'rule' => 'minLength: { value: 8, message: "config.form.{name}.validation.minLength" }',
            ],
            'max_length' => [
                'condition' => fn ($f): bool => in_array($f['type'], ['text', 'textarea', 'password', 'email']) || in_array($f['name'], ['roles', 'permissions']),
                'rule' => 'maxLength: { value: 255, message: "config.form.{name}.validation.maxLength" }',
            ],
            'id_field' => [
                'condition' => fn ($f): bool => in_array($f['name'], ['reportToId', 'mainOrganizationId', 'createdBy', 'updatedBy']),
                'rule' => 'pattern: { value: /^\d+$/, message: "config.form.{name}.validation.pattern" }',
            ],
            'file' => [
                'condition' => fn ($f): bool => $f['name'] === 'attachment',
                'rule' => 'mimes: { value: ["jpg", "jpeg", "png"], message: "config.form.{name}.validation.mimes" }',
            ],
            'file_size' => [
                'condition' => fn ($f): bool => $f['name'] === 'attachment',
                'rule' => 'maxSize: { value: 5120, message: "config.form.{name}.validation.maxSize" }',
            ],
            'enum' => [
                'condition' => fn ($f): bool => !empty($f['options']),
                'rule' => 'in: { value: {options}, message: "config.form.{name}.validation.in" }',
            ],
        ]);

        foreach ($validationConfigs as $key => $config) {
            if ($config['condition']($field)) {
                $messageKey = match ($key) {
                    'required' => 'required',
                    'email' => 'email',
                    'password' => 'minLength',
                    'id_field' => 'pattern',
                    'max_length' => 'maxLength',
                    'file' => 'mimes',
                    'file_size' => 'maxSize',
                    'enum' => 'in',
                    default => $key,
                };
                $message = match ($key) {
                    'required' => "{$label} is required",
                    'email' => 'Invalid email format',
                    'password' => 'Password must be at least 8 characters',
                    'id_field' => "{$label} must be a number",
                    'max_length' => "{$label} must be 255 characters or less",
                    'file' => "{$label} must be a valid image (jpg, jpeg, png)",
                    'file_size' => "{$label} must be less than 5MB",
                    'enum' => "{$label} must be one of the allowed values",
                    default => "{$label} is invalid",
                };
                $validations[] = "\"{$messageKey}\": \"{$message}\"";
            }
        }

        $optionsBlock = '';
        if (!empty($field['options'])) {
            $enumLabels = config('frontend_generator.enum_labels', []);
            $optionsTranslations = array_map(
                fn (string $opt): string => sprintf('"%s": "%s"', $opt, $this->generateEnumLabel($opt, $name, $enumLabels)),
                $field['options']
            );
            $optionsBlock = ', "options": {'.implode(', ', $optionsTranslations).'}';
        }

        $validationBlock = $validations !== [] ? ', "validation": {'.implode(', ', $validations).'}' : '';

        return sprintf('"%s": {"label": "%s"%s%s}', $name, $label, $validationBlock, $optionsBlock);
    }

    private function generateEnumLabel(string $value, string $fieldName, array $enumLabels): string
    {
        if (isset($enumLabels[$fieldName][$value])) {
            return $enumLabels[$fieldName][$value];
        }

        $commonMappings = [
            'locale' => [
                'en' => 'English',
                'dr' => 'Dari',
                'ps' => 'Pashto',
            ],
        ];

        if (isset($commonMappings[$fieldName][$value])) {
            return $commonMappings[$fieldName][$value];
        }

        $words = preg_split('/[_-]/', $value);

        return implode(' ', array_map(fn ($w): string => ucfirst(strtolower($w)), $words));
    }

    public function generateTableTranslations(string $entity): string
    {
        $entityHeadLine = Str::headline($entity);
        $items = [
            "\"add\": \"Add {$entityHeadLine}\"",
            "\"create\": \"Create {$entityHeadLine}\"",
            "\"edit\": \"Edit {$entityHeadLine}\"",
            "\"update\": \"Update {$entityHeadLine}\"",
        ];

        return implode(self::LINE_SEPARATOR, $items);
    }

    public function generateRepositoryTranslations(string $entity): string
    {
        $entityHeadLine = Str::headline($entity);
        $entityHeadLinePlural = Str::plural($entityHeadLine);
        $items = [
            "\"createFailed\": \"Failed to create {$entityHeadLine}\"",
            "\"fetchFailed\": \"Failed to fetch {$entityHeadLine}\"",
            "\"updateFailed\": \"Failed to update {$entityHeadLine}\"",
            "\"deleteFailed\": \"Failed to delete {$entityHeadLine}\"",
            "\"fetchManyFailed\": \"Failed to fetch {$entityHeadLinePlural}\"",
            "\"fetchManySuccess\": \"{$entityHeadLinePlural} fetched successfully\"",
        ];

        return implode(self::LINE_SEPARATOR, $items);
    }

    public function generatePresentationTranslations(string $entity): string
    {
        $entityHeadLine = Str::headline($entity);
        $items = [
            "\"createSuccess\": \"{$entityHeadLine} created successfully\"",
            "\"updateSuccess\": \"{$entityHeadLine} updated successfully\"",
            "\"deleteSuccess\": \"{$entityHeadLine} deleted successfully\"",
        ];

        return implode(self::LINE_SEPARATOR, $items);
    }
}
