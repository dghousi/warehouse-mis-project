<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class RequestParser
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function parse(string $module, string $entity, Command $command, Logger $logger): array
    {
        $requestPath = base_path("app/Modules/{$module}/Infrastructure/Http/Requests/{$entity}/Store{$entity}Request.php");

        if (!File::exists($requestPath)) {
            $logger->error("Request file for {$entity} not found at {$requestPath}.", $command);

            return [];
        }

        $logger->info("Parsing request file: {$requestPath}", $command);
        $content = File::get($requestPath);

        $rulesContent = $this->extractRulesContent($content, $requestPath, $command, $logger);
        if (in_array($rulesContent, [null, '', '0'], true)) {
            return [];
        }

        $constants = $this->extractConstants($content);

        return $this->parseRules($rulesContent, $constants);
    }

    private function extractRulesContent(string $content, string $requestPath, Command $command, Logger $logger): ?string
    {
        preg_match("/public function rules\(\): array\s*{([^}]+)}/s", $content, $rulesMatch);
        if ($rulesMatch === []) {
            $logger->error("Could not parse rules method in {$requestPath}.", $command);

            return null;
        }

        return $rulesMatch[1];
    }

    private function extractConstants(string $content): array
    {
        preg_match_all("/private const (\w+) = '([^']+)';/", $content, $constMatches, PREG_SET_ORDER);
        $constants = [];
        foreach ($constMatches as $constMatch) {
            $constants[$constMatch[1]] = $constMatch[2];
        }

        return $constants;
    }

    private function parseRules(string $rulesContent, array $constants): array
    {
        preg_match_all("/'(\w+)'\s*=>\s*(\[[^\]]+\]|'[^']+'|self::\w+)/", $rulesContent, $ruleMatches, PREG_SET_ORDER);
        $fields = [];

        foreach ($ruleMatches as $ruleMatch) {
            $fieldName = $ruleMatch[1];
            $rule = trim($ruleMatch[2]);
            $fields[$fieldName] = ['required' => false];

            $ruleArray = $this->normalizeRule($rule, $constants);
            $fields[$fieldName]['required'] = $this->isRequired($ruleArray);
            $fields[$fieldName] = array_merge($fields[$fieldName], $this->extractEnum($ruleArray));
        }

        return $fields;
    }

    private function normalizeRule(string $rule, array $constants): array
    {
        if (str_starts_with($rule, '[')) {
            $ruleString = trim($rule, '[]');

            return array_map(fn ($r): string => trim($r, "'\""), array_map(trim(...), explode(',', $ruleString)));
        }

        if (str_starts_with($rule, 'self::')) {
            $constName = substr($rule, 6);
            $rule = $constants[$constName] ?? $rule;
        }

        return explode('|', trim((string) $rule, "'"));
    }

    private function isRequired(array $ruleArray): bool
    {
        return in_array('required', $ruleArray) && !in_array('nullable', $ruleArray);
    }

    private function extractEnum(array $ruleArray): array
    {
        foreach ($ruleArray as $r) {
            if (str_starts_with((string) $r, 'in:')) {
                return [
                    'enum' => array_map(
                        fn ($value): string => trim(trim($value, "'\""), ", \t\n\r\0\x0B"),
                        explode(',', substr((string) $r, 3))
                    ),
                ];
            }
        }

        return [];
    }
}
