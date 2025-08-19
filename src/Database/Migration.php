<?php

namespace Apileon\Database;

abstract class Migration
{
    protected QueryBuilder $query;

    public function __construct()
    {
        $this->query = new QueryBuilder();
    }

    abstract public function up(): void;
    abstract public function down(): void;

    protected function createTable(string $tableName, callable $callback): void
    {
        $schema = new Schema($tableName);
        $callback($schema);
        
        $sql = $schema->toSql();
        $this->executeRaw($sql);
    }

    protected function dropTable(string $tableName): void
    {
        $this->executeRaw("DROP TABLE IF EXISTS `{$tableName}`");
    }

    protected function executeRaw(string $sql): void
    {
        DatabaseManager::getConnection()->exec($sql);
    }
}

class Schema
{
    private string $tableName;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private string $engine = 'InnoDB';
    private string $charset = 'utf8mb4';
    private string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function id(string $name = 'id'): self
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` VARCHAR({$length})");
    }

    public function text(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` TEXT");
    }

    public function longText(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` LONGTEXT");
    }

    public function integer(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` INT");
    }

    public function bigInteger(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` BIGINT");
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` DECIMAL({$precision},{$scale})");
    }

    public function boolean(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` BOOLEAN");
    }

    public function date(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` DATE");
    }

    public function dateTime(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` DATETIME");
    }

    public function timestamp(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` TIMESTAMP");
    }

    public function timestamps(): self
    {
        $this->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->default('CURRENT_TIMESTAMP')->onUpdate('CURRENT_TIMESTAMP');
        return $this;
    }

    public function json(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, "`{$name}` JSON");
    }

    public function enum(string $name, array $values): ColumnDefinition
    {
        $enumValues = implode(',', array_map(fn($v) => "'{$v}'", $values));
        return new ColumnDefinition($this, "`{$name}` ENUM({$enumValues})");
    }

    public function index(string|array $columns, string $name = null): self
    {
        if (is_array($columns)) {
            $columns = '`' . implode('`,`', $columns) . '`';
            $name = $name ?: 'idx_' . implode('_', func_get_arg(0));
        } else {
            $columns = "`{$columns}`";
            $name = $name ?: "idx_{$columns}";
        }

        $this->indexes[] = "INDEX `{$name}` ({$columns})";
        return $this;
    }

    public function unique(string|array $columns, string $name = null): self
    {
        if (is_array($columns)) {
            $columns = '`' . implode('`,`', $columns) . '`';
            $name = $name ?: 'unique_' . implode('_', func_get_arg(0));
        } else {
            $columns = "`{$columns}`";
            $name = $name ?: "unique_{$columns}";
        }

        $this->indexes[] = "UNIQUE INDEX `{$name}` ({$columns})";
        return $this;
    }

    public function foreign(string $column): ForeignKeyDefinition
    {
        return new ForeignKeyDefinition($this, $column);
    }

    public function addColumn(string $definition): void
    {
        $this->columns[] = $definition;
    }

    public function addIndex(string $definition): void
    {
        $this->indexes[] = $definition;
    }

    public function addForeignKey(string $definition): void
    {
        $this->foreignKeys[] = $definition;
    }

    public function toSql(): string
    {
        $sql = "CREATE TABLE `{$this->tableName}` (\n";
        
        // Add columns
        $parts = array_merge($this->columns, $this->indexes, $this->foreignKeys);
        $sql .= "    " . implode(",\n    ", $parts) . "\n";
        
        $sql .= ") ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation}";
        
        return $sql;
    }
}

class ColumnDefinition
{
    private Schema $schema;
    private string $definition;

    public function __construct(Schema $schema, string $definition)
    {
        $this->schema = $schema;
        $this->definition = $definition;
    }

    public function nullable(): self
    {
        $this->definition .= " NULL";
        $this->finalize();
        return $this;
    }

    public function notNull(): self
    {
        $this->definition .= " NOT NULL";
        $this->finalize();
        return $this;
    }

    public function default($value): self
    {
        if (is_string($value) && $value !== 'CURRENT_TIMESTAMP') {
            $value = "'{$value}'";
        } elseif (is_null($value)) {
            $value = 'NULL';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        
        $this->definition .= " DEFAULT {$value}";
        $this->finalize();
        return $this;
    }

    public function onUpdate(string $value): self
    {
        $this->definition .= " ON UPDATE {$value}";
        $this->finalize();
        return $this;
    }

    public function unsigned(): self
    {
        $this->definition .= " UNSIGNED";
        return $this;
    }

    public function autoIncrement(): self
    {
        $this->definition .= " AUTO_INCREMENT";
        return $this;
    }

    public function primary(): self
    {
        $this->definition .= " PRIMARY KEY";
        $this->finalize();
        return $this;
    }

    public function unique(): self
    {
        $this->definition .= " UNIQUE";
        $this->finalize();
        return $this;
    }

    private function finalize(): void
    {
        $this->schema->addColumn($this->definition);
    }

    public function __destruct()
    {
        if (strpos($this->definition, 'NULL') === false && 
            strpos($this->definition, 'PRIMARY KEY') === false &&
            strpos($this->definition, 'AUTO_INCREMENT') === false) {
            $this->notNull();
        } else {
            $this->finalize();
        }
    }
}

class ForeignKeyDefinition
{
    private Schema $schema;
    private string $column;
    private string $definition;

    public function __construct(Schema $schema, string $column)
    {
        $this->schema = $schema;
        $this->column = $column;
        $this->definition = "FOREIGN KEY (`{$column}`)";
    }

    public function references(string $column): self
    {
        $this->definition .= " REFERENCES {$column}";
        return $this;
    }

    public function on(string $table): self
    {
        $this->definition = str_replace(" REFERENCES ", " REFERENCES `{$table}` (", $this->definition) . ")";
        return $this;
    }

    public function onDelete(string $action): self
    {
        $this->definition .= " ON DELETE {$action}";
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $this->definition .= " ON UPDATE {$action}";
        return $this;
    }

    public function cascadeOnDelete(): self
    {
        return $this->onDelete('CASCADE');
    }

    public function cascadeOnUpdate(): self
    {
        return $this->onUpdate('CASCADE');
    }

    public function __destruct()
    {
        $this->schema->addForeignKey($this->definition);
    }
}
