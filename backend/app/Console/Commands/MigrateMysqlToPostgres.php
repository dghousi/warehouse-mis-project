<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Migration\DataMapper;
use App\Console\Commands\Migration\TableValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

final class MigrateMysqlToPostgres extends Command
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    protected $signature = 'migrate:mysql-to-postgres {table? : Specific table to migrate} {--include-soft-deleted : Include soft-deleted records} {--skip-duplicates : Skip duplicate key records} {--truncate-target : Truncate target table before migration} {--silent : Suppress console output}';

    protected $description = 'Migrate data from MySQL/MariaDB to PostgreSQL using mapping configuration';

    public function __construct(protected TableValidator $tableValidator, protected DataMapper $dataMapper)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $mapping = config('migration_mapping.tables');
        if (empty($mapping)) {
            $this->error('No migration mapping configuration found');

            return;
        }

        $tables = $this->getTablesToMigrate($mapping);
        $this->migrateTables($tables);
    }

    private function getTablesToMigrate(array $mapping): array
    {
        $tableArg = $this->argument('table');

        if ($tableArg) {
            $tableConfig = $mapping[$tableArg] ?? null;

            if (!$tableConfig) {
                throw new InvalidArgumentException("No mapping found for table: {$tableArg}");
            }

            return [$tableArg => $tableConfig];
        }

        return $mapping;
    }

    private function migrateTables(array $tables): void
    {
        foreach ($tables as $sourceTable => $config) {
            if (!$this->tableValidator->validateTableConfig($sourceTable, $config, $this)) {
                continue;
            }

            $this->migrateTable($sourceTable, $config);
        }
    }

    private function migrateTable(string $sourceTable, array $config): void
    {
        $targetTable = $config['new_name'];
        $columns = $config['columns'];
        $orderBy = $config['order_by'] ?? 'id';

        $this->logInfo("Migrating [{$sourceTable}] → [{$targetTable}]...");

        if ($this->option('truncate-target')) {
            DB::connection('pgsql')->table($targetTable)->truncate();
            $this->logInfo("Truncated target table [{$targetTable}]");
        }

        DB::connection('pgsql')->statement('SET CONSTRAINTS ALL DEFERRED');

        try {
            $stats = $this->processTableData($sourceTable, $targetTable, $columns, $orderBy);
            $this->logInfo("Migrated {$stats['rowCount']} rows from [{$sourceTable}] to [{$targetTable}] (Retrieved: {$stats['retrievedRows']}, Skipped: {$stats['skippedRows']})");
        } finally {
            try {
                DB::connection('pgsql')->statement('SET CONSTRAINTS ALL IMMEDIATE');
            } catch (\Exception $e) {
                $this->error("Failed to re-enable constraints: {$e->getMessage()}");
            }

            $this->resetSequence($targetTable);

        }
    }

    private function processTableData(string $sourceTable, string $targetTable, array $columns, string|array $orderBy): array
    {
        $stats = ['rowCount' => 0, 'retrievedRows' => 0, 'skippedRows' => 0];
        $batch = [];
        $existingKeys = $this->getExistingKeys($targetTable);
        $validIds = $this->getValidIds($sourceTable, $targetTable);

        $builder = $this->buildQuery($sourceTable, $orderBy);
        $builder->chunk(1000, function (Collection $rows) use ($columns, $targetTable, &$batch, &$stats, $existingKeys, $validIds): void {
            $stats['retrievedRows'] += $rows->count();
            $this->processRows($rows, $columns, $targetTable, $batch, $stats, $existingKeys, $validIds);
        });

        if ($batch !== []) {
            $this->insertBatch($targetTable, $batch, $stats['rowCount']);
        }

        return $stats;
    }

    private function buildQuery(string $sourceTable, string|array $orderBy): \Illuminate\Database\Query\Builder
    {
        $builder = DB::connection('mariadb')->table($sourceTable);

        if ($sourceTable === 'users' && !$this->option('include-soft-deleted')) {
            $builder->whereNull('deleted_at');
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $column) {
                if (!Schema::connection('mariadb')->hasColumn($sourceTable, $column)) {
                    $this->logWarning("Order by column [{$column}] not found in [{$sourceTable}]. Using non-chunked query.");

                    return DB::connection('mariadb')->table($sourceTable);
                }
                $builder->orderBy($column);
            }
        } elseif (!Schema::connection('mariadb')->hasColumn($sourceTable, $orderBy)) {
            $this->logWarning("Order by column [{$orderBy}] not found in [{$sourceTable}]. Using non-chunked query.");

            return DB::connection('mariadb')->table($sourceTable);
        } else {
            $builder->orderBy($orderBy);
        }

        return $builder;
    }

    private function getExistingKeys(string $targetTable): array
    {
        if ($this->option('truncate-target')) {
            return [];
        }

        $keyColumn = $this->getKeyColumn($targetTable);

        return $keyColumn ? DB::connection('pgsql')->table($targetTable)->pluck($keyColumn)->toArray() : [];
    }

    private function getValidIds(string $sourceTable, string $targetTable): array
    {
        if ($targetTable === 'users') {
            return DB::connection('mariadb')->table($sourceTable)->pluck('id')->toArray();
        }
        if ($targetTable === 'roles') {
            return DB::connection('mariadb')->table('modules')->pluck('id')->toArray();
        }

        return [];
    }

    private function getKeyColumn(string $targetTable): ?string
    {
        return match ($targetTable) {
            'roles' => 'name',
            'users' => 'email',
            default => null,
        };
    }

    private function processRows(Collection $rows, array $columns, string $targetTable, array &$batch, array &$stats, array $existingKeys, array $validIds): void
    {
        foreach ($rows as $row) {
            $data = $this->dataMapper->mapRowData($row, $columns, $targetTable, $validIds);

            $keyColumn = $this->getKeyColumn($targetTable);
            if ($keyColumn && $this->option('skip-duplicates') && in_array($data[$keyColumn] ?? null, $existingKeys)) {
                $this->logWarning("Skipping duplicate {$keyColumn}: {$data[$keyColumn]}");
                $stats['skippedRows']++;

                continue;
            }

            $batch[] = $data;

            if (count($batch) >= 10) {
                $this->insertBatch($targetTable, $batch, $stats['rowCount']);
                if ($keyColumn) {
                    $existingKeys = array_merge($existingKeys, array_column($batch, $keyColumn));
                }
                $batch = [];
            }
        }
    }

    private function insertBatch(string $targetTable, array &$batch, int &$rowCount): void
    {
        try {
            DB::connection('pgsql')->transaction(function () use ($targetTable, $batch, &$rowCount): void {
                DB::connection('pgsql')->table($targetTable)->insert($batch);
                $rowCount += count($batch);
            });
        } catch (\Exception $e) {
            $this->error('Failed to insert '.count($batch)." rows into [{$targetTable}]: {$e->getMessage()}");
            $this->error('First failing row: '.json_encode($batch[0], JSON_PRETTY_PRINT));
            Log::error("Migration error for [{$targetTable}]: {$e->getMessage()}", ['row' => $batch[0]]);
        }
        $batch = [];
    }

    private function resetSequence(string $table): void
    {
        try {
            $connection = DB::connection('pgsql');
            $sequenceName = $connection->selectOne("
                SELECT pg_get_serial_sequence(?, 'id') AS seq_name
            ", [$table])->seq_name ?? null;

            if (!$sequenceName) {
                $this->logWarning("No sequence found for table [{$table}], skipping sequence reset.");

                return;
            }

            $maxId = $connection->table($table)->max('id') ?? 0;
            $newValue = $maxId + 1;

            $connection->statement('SELECT setval(?, ?, false)', [$sequenceName, $newValue]);

            $this->logInfo("Sequence for [{$table}] reset to {$newValue}");
        } catch (\Exception $e) {
            $this->error("Failed to reset sequence for [{$table}]: {$e->getMessage()}");
        }
    }

    private function logInfo(string $message): void
    {
        if (!$this->option('silent')) {
            $this->info($message);
        }
        Log::info($message);
    }

    public function logWarning(string $message): void
    {
        if (!$this->option('silent')) {
            $this->warn($message);
        }
        Log::warning($message);
    }

    public function error($string, $verbosity = null): void
    {
        parent::error($string, $verbosity);
        Log::error(is_array($string) ? json_encode($string) : $string);
    }
}
