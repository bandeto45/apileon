<?php

namespace Apileon\Database;

class MigrationRunner
{
    private string $migrationsPath;
    private array $executed = [];

    public function __construct(string $migrationsPath = null)
    {
        $this->migrationsPath = $migrationsPath ?: __DIR__ . '/../../database/migrations';
        $this->createMigrationsTable();
        $this->loadExecutedMigrations();
    }

    public function migrate(): void
    {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "No pending migrations.\n";
            return;
        }

        echo "Running " . count($migrations) . " migrations...\n";

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }

        echo "All migrations completed successfully.\n";
    }

    public function rollback(int $steps = 1): void
    {
        $executedMigrations = array_reverse($this->executed);
        $migrationsToRollback = array_slice($executedMigrations, 0, $steps);

        if (empty($migrationsToRollback)) {
            echo "No migrations to rollback.\n";
            return;
        }

        echo "Rolling back " . count($migrationsToRollback) . " migrations...\n";

        foreach ($migrationsToRollback as $migrationName) {
            $this->rollbackMigration($migrationName);
        }

        echo "Rollback completed successfully.\n";
    }

    public function refresh(): void
    {
        echo "Refreshing database...\n";
        
        // Rollback all migrations
        $this->rollback(count($this->executed));
        
        // Run all migrations
        $this->migrate();
    }

    public function seed(): void
    {
        $seederFile = __DIR__ . '/../../database/seeders/DatabaseSeeder.php';
        
        if (!file_exists($seederFile)) {
            echo "No database seeder found.\n";
            return;
        }

        require_once $seederFile;
        
        echo "Seeding database...\n";
        (new \DatabaseSeeder())->run();
        echo "Database seeded successfully.\n";
    }

    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `migrations_migration_unique` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        DatabaseManager::getConnection()->exec($sql);
    }

    private function loadExecutedMigrations(): void
    {
        $query = new QueryBuilder();
        $results = $query->table('migrations')
                        ->select(['migration'])
                        ->orderBy('batch')
                        ->orderBy('id')
                        ->get();

        $this->executed = array_column($results, 'migration');
    }

    private function getPendingMigrations(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $pending = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            
            if (!in_array($migrationName, $this->executed)) {
                $pending[] = $file;
            }
        }

        sort($pending);
        return $pending;
    }

    private function runMigration(string $file): void
    {
        $migrationName = basename($file, '.php');
        
        echo "Migrating: {$migrationName}... ";

        try {
            DatabaseManager::beginTransaction();

            // Include the migration file
            require_once $file;

            // Extract class name from migration file
            $className = $this->getClassNameFromFile($file);
            
            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class {$className} not found in {$file}");
            }

            // Run the migration
            $migration = new $className();
            $migration->up();

            // Record the migration
            $this->recordMigration($migrationName);

            DatabaseManager::commit();
            echo "✓\n";

        } catch (\Exception $e) {
            DatabaseManager::rollback();
            echo "✗\n";
            throw new \RuntimeException("Migration {$migrationName} failed: " . $e->getMessage());
        }
    }

    private function rollbackMigration(string $migrationName): void
    {
        echo "Rolling back: {$migrationName}... ";

        try {
            DatabaseManager::beginTransaction();

            // Find the migration file
            $file = $this->migrationsPath . '/' . $migrationName . '.php';
            
            if (!file_exists($file)) {
                throw new \RuntimeException("Migration file not found: {$file}");
            }

            // Include the migration file
            require_once $file;

            // Extract class name from migration file
            $className = $this->getClassNameFromFile($file);
            
            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class {$className} not found");
            }

            // Run the rollback
            $migration = new $className();
            $migration->down();

            // Remove the migration record
            $this->removeMigrationRecord($migrationName);

            DatabaseManager::commit();
            echo "✓\n";

        } catch (\Exception $e) {
            DatabaseManager::rollback();
            echo "✗\n";
            throw new \RuntimeException("Rollback {$migrationName} failed: " . $e->getMessage());
        }
    }

    private function recordMigration(string $migrationName): void
    {
        $query = new QueryBuilder();
        $maxBatch = $query->table('migrations')->count() > 0 
                   ? $query->table('migrations')->max('batch') 
                   : 0;

        $query->table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $maxBatch + 1
        ]);

        $this->executed[] = $migrationName;
    }

    private function removeMigrationRecord(string $migrationName): void
    {
        $query = new QueryBuilder();
        $query->table('migrations')->where('migration', $migrationName)->delete();

        $this->executed = array_diff($this->executed, [$migrationName]);
    }

    private function getClassNameFromFile(string $file): string
    {
        $content = file_get_contents($file);
        
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        throw new \RuntimeException("Could not extract class name from {$file}");
    }

    public function status(): void
    {
        echo "Migration Status:\n";
        echo "================\n";

        $files = glob($this->migrationsPath . '/*.php');
        
        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            $status = in_array($migrationName, $this->executed) ? '✓ Ran' : '✗ Pending';
            echo "{$status} {$migrationName}\n";
        }

        echo "\nTotal migrations: " . count($files) . "\n";
        echo "Executed: " . count($this->executed) . "\n";
        echo "Pending: " . (count($files) - count($this->executed)) . "\n";
    }
}
