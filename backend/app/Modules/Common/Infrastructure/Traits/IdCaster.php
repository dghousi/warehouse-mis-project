<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Traits;

trait IdCaster
{
    protected static function castId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        throw new \InvalidArgumentException("Invalid ID value: {$value}");
    }

    protected static function castIds(array $keys, array $data): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = isset($data[$key]) ? self::castId($data[$key]) : null;
        }

        return $result;
    }
}
