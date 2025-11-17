<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Exception;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionProperty;

final class DtoParser
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function parse(string $dtoPath, string $module, string $entity): array
    {
        $this->ensureDtoFileExists($dtoPath);

        require_once $dtoPath; // NOSONAR

        $className = $this->resolveClassName($module, $entity);
        $this->ensureDtoClassExists($className, $dtoPath);

        $reflection = new ReflectionClass($className);
        $defaults = $this->extractDefaults(File::get($dtoPath));

        return $this->extractFields($reflection, $defaults);
    }

    private function ensureDtoFileExists(string $dtoPath): void
    {
        if (!File::exists($dtoPath)) {
            throw new Exception("DTO file not found at {$dtoPath}"); // NOSONAR
        }
    }

    private function resolveClassName(string $module, string $entity): string
    {
        return "App\\Modules\\{$module}\\Application\\DTOs\\{$entity}Data";
    }

    private function ensureDtoClassExists(string $className, string $dtoPath): void
    {
        if (!class_exists($className)) {
            throw new Exception("Class {$className} not found in {$dtoPath}"); // NOSONAR
        }
    }

    private function extractDefaults(string $content): array
    {
        $defaults = [];
        preg_match_all(
            '/\$this->(\w+)\s*=\s*\$data\s*\[\s*[\'"](\w+)[\'"]\s*\]\s*\?\?\s*([^;]+);/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $defaults[$match[1]] = trim($match[3]);
        }

        return $defaults;
    }

    private function extractFields(ReflectionClass $reflectionClass, array $defaults): array
    {
        $fields = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $type = $reflectionProperty->hasType() ? $reflectionProperty->getType()->getName() : 'string';
            $defaultValue = $defaults[$name] ?? null;

            $fields[] = [
                'name' => $name,
                'type' => $this->mapDtoTypeToFrontend($type, $name),
                'required' => false,
                'default' => $defaultValue ?? 'null',
            ];
        }

        return $fields;
    }

    private function mapDtoTypeToFrontend(string $type, string $name): string
    {
        $specialFields = [
            'emailVerifiedAt' => 'datetime-local',
            'lastLoginAt' => 'datetime-local',
            'roles' => 'multiselect',
            'permissions' => 'multiselect',
            'attachment' => 'file',
        ];

        if (isset($specialFields[$name])) {
            return $specialFields[$name];
        }

        $clean = strtolower(trim($type, '\\?'));
        $mappings = config('frontend_generator.type_mappings', []);

        return $mappings[$clean] ?? $mappings['default'] ?? 'text';
    }
}
