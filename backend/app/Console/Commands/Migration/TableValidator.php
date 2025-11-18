<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Console\Commands\MigrateMysqlToPostgres;
use Illuminate\Support\Facades\Schema;

final class TableValidator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function validateTableConfig(string $sourceTable, ?array $config, MigrateMysqlToPostgres $migrateMysqlToPostgres): bool
    {
        if (!$config || empty($config['new_name'])) {
            $migrateMysqlToPostgres->logWarning("Skipping table: {$sourceTable} (no mapping found)");

            return false;
        }

        $targetTable = $config['new_name'];
        $isValid = true;

        if (!Schema::connection('mariadb')->hasTable($sourceTable)) {
            $migrateMysqlToPostgres->error("Source table [{$sourceTable}] does not exist in MariaDB");
            $isValid = false;
        } elseif (!Schema::connection('pgsql')->hasTable($targetTable)) {
            $migrateMysqlToPostgres->error("Target table [{$targetTable}] does not exist in PostgreSQL");
            $isValid = false;
        }

        return $isValid;
    }
}
