<?php

namespace Apileon\Database;

use PDO;
use PDOStatement;
use InvalidArgumentException;

class QueryBuilder
{
    protected PDO $connection;
    protected string $table = '';
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $selects = ['*'];
    protected array $joins = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $groups = [];

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?: DatabaseManager::getConnection();
    }

    public function table(string $table): self
    {
        $this->table = $this->escapeIdentifier($table);
        return $this;
    }

    public function select(array|string $columns = ['*']): self
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }
        
        $this->selects = array_map([$this, 'escapeIdentifier'], $columns);
        return $this;
    }

    public function where(string $column, string $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = $this->generatePlaceholder();
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $this->escapeIdentifier($column),
            'operator' => $this->validateOperator($operator),
            'placeholder' => $placeholder,
            'boolean' => 'AND'
        ];
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Values array cannot be empty for whereIn');
        }

        $placeholders = [];
        foreach ($values as $value) {
            $placeholder = $this->generatePlaceholder();
            $placeholders[] = $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $this->wheres[] = [
            'type' => 'in',
            'column' => $this->escapeIdentifier($column),
            'placeholders' => $placeholders,
            'boolean' => 'AND'
        ];

        return $this;
    }

    public function orWhere(string $column, string $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = $this->generatePlaceholder();
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $this->escapeIdentifier($column),
            'operator' => $this->validateOperator($operator),
            'placeholder' => $placeholder,
            'boolean' => 'OR'
        ];
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $this->escapeIdentifier($table),
            'first' => $this->escapeIdentifier($first),
            'operator' => $this->validateOperator($operator),
            'second' => $this->escapeIdentifier($second)
        ];

        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $this->escapeIdentifier($table),
            'first' => $this->escapeIdentifier($first),
            'operator' => $this->validateOperator($operator),
            'second' => $this->escapeIdentifier($second)
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException('Order direction must be ASC or DESC');
        }

        $this->orders[] = $this->escapeIdentifier($column) . ' ' . $direction;
        return $this;
    }

    public function groupBy(string $column): self
    {
        $this->groups[] = $this->escapeIdentifier($column);
        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('Limit must be non-negative');
        }
        
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be non-negative');
        }
        
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $statement = $this->executeQuery($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function find(int $id, string $column = 'id'): ?array
    {
        return $this->where($column, $id)->first();
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $result = $this->first();
        return (int) ($result['count'] ?? 0);
    }

    public function insert(array $data): bool
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Insert data cannot be empty');
        }

        $columns = array_keys($data);
        $placeholders = array_map(function($col) {
            return $this->generatePlaceholder();
        }, $columns);

        $columnNames = implode(', ', array_map([$this, 'escapeIdentifier'], $columns));
        $placeholderNames = implode(', ', $placeholders);

        $sql = "INSERT INTO {$this->table} ({$columnNames}) VALUES ({$placeholderNames})";

        $this->bindings = array_combine($placeholders, array_values($data));

        $statement = $this->executeQuery($sql);
        return $statement->rowCount() > 0;
    }

    public function insertGetId(array $data): int
    {
        $this->insert($data);
        return (int) $this->connection->lastInsertId();
    }

    public function update(array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty');
        }

        if (empty($this->wheres)) {
            throw new InvalidArgumentException('Update requires at least one WHERE clause for safety');
        }

        $sets = [];
        foreach ($data as $column => $value) {
            $placeholder = $this->generatePlaceholder();
            $sets[] = $this->escapeIdentifier($column) . ' = ' . $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        $sql .= $this->buildWhereClause();

        $statement = $this->executeQuery($sql);
        return $statement->rowCount();
    }

    public function delete(): int
    {
        if (empty($this->wheres)) {
            throw new InvalidArgumentException('Delete requires at least one WHERE clause for safety');
        }

        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        $statement = $this->executeQuery($sql);
        return $statement->rowCount();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    protected function buildSelectQuery(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->selects);
        $sql .= ' FROM ' . $this->table;

        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        $sql .= $this->buildWhereClause();

        // Add group by
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        // Add order by
        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        // Add limit and offset
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    protected function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $conditions = [];
        foreach ($this->wheres as $index => $where) {
            $condition = '';
            
            if ($index > 0) {
                $condition .= ' ' . $where['boolean'] . ' ';
            }

            if ($where['type'] === 'basic') {
                $condition .= "{$where['column']} {$where['operator']} {$where['placeholder']}";
            } elseif ($where['type'] === 'in') {
                $condition .= "{$where['column']} IN (" . implode(', ', $where['placeholders']) . ")";
            }

            $conditions[] = $condition;
        }

        return ' WHERE ' . implode('', $conditions);
    }

    protected function executeQuery(string $sql): PDOStatement
    {
        try {
            $statement = $this->connection->prepare($sql);
            
            foreach ($this->bindings as $placeholder => $value) {
                $statement->bindValue($placeholder, $value, $this->getPdoType($value));
            }

            $statement->execute();
            return $statement;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Query execution failed: " . $e->getMessage() . " | SQL: " . $sql);
        }
    }

    protected function escapeIdentifier(string $identifier): string
    {
        // Remove any potential SQL injection attempts
        $identifier = preg_replace('/[^a-zA-Z0-9_.]/', '', $identifier);
        
        // Handle table.column format
        if (strpos($identifier, '.') !== false) {
            $parts = explode('.', $identifier);
            return '`' . implode('`.`', $parts) . '`';
        }

        return '`' . $identifier . '`';
    }

    protected function validateOperator(string $operator): string
    {
        $allowedOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
        
        if (!in_array(strtoupper($operator), array_map('strtoupper', $allowedOperators))) {
            throw new InvalidArgumentException("Invalid operator: {$operator}");
        }

        return strtoupper($operator);
    }

    protected function generatePlaceholder(): string
    {
        return ':param_' . uniqid();
    }

    protected function getPdoType($value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }

    public function toSql(): string
    {
        return $this->buildSelectQuery();
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
