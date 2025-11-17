<?php

declare(strict_types=1);

namespace App\Helpers;

final class ValidationHelper
{
    // Check if field is required
    public static function requiredCondition(array $field)
    {
        return $field['required'] ?? false;
    }

    // Check if field is email
    public static function emailCondition(array $field): bool
    {
        return $field['name'] === 'email';
    }

    // Check if field is password
    public static function passwordCondition(array $field): bool
    {
        return $field['name'] === 'password';
    }

    // Check if field is an ID field
    public static function idFieldCondition(array $field): bool
    {
        return str_ends_with((string) $field['name'], 'Id') || str_ends_with((string) $field['name'], 'By');
    }

    // Check if field has max length
    public static function maxLengthCondition(array $field): bool
    {
        return in_array($field['type'], ['text', 'email', 'password', 'textarea', 'multiselect']);
    }

    // Check if field is a file attachment
    public static function fileCondition(array $field): bool
    {
        return $field['name'] === 'attachment';
    }

    // Check if field size is within the limit
    public static function fileSizeCondition(array $field): bool
    {
        return $field['name'] === 'attachment';
    }

    // Check if field has options for enum validation
    public static function enumCondition(array $field): bool
    {
        return !empty($field['options']);
    }
}
