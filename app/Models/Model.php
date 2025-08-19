<?php

namespace App\Models;

use Apileon\Database\DatabaseManager;
use Apileon\Database\QueryBuilder;
use InvalidArgumentException;
use RuntimeException;

abstract class Model
{
    protected array $attributes = [];
    protected array $original = [];
    protected array $fillable = [];
    protected array $guarded = ['id'];
    protected array $hidden = [];
    protected array $casts = [];
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected bool $exists = false;
    protected array $timestamps = ['created_at', 'updated_at'];
    protected bool $useTimestamps = true;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->syncOriginal();
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }

    public function getAttribute(string $key)
    {
        $value = $this->attributes[$key] ?? null;
        
        if ($value !== null && isset($this->casts[$key])) {
            return $this->castAttribute($key, $value);
        }
        
        return $value;
    }

    public function setAttribute(string $key, $value): void
    {
        if (isset($this->casts[$key])) {
            $value = $this->castAttributeForStorage($key, $value);
        }
        
        $this->attributes[$key] = $value;
    }

    protected function castAttribute(string $key, $value)
    {
        $castType = $this->casts[$key];
        
        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'datetime':
                return $value instanceof \DateTime ? $value : new \DateTime($value);
            default:
                return $value;
        }
    }

    protected function castAttributeForStorage(string $key, $value)
    {
        $castType = $this->casts[$key];
        
        switch ($castType) {
            case 'array':
            case 'json':
                return is_array($value) ? json_encode($value) : $value;
            case 'datetime':
                if ($value instanceof \DateTime) {
                    return $value->format('Y-m-d H:i:s');
                }
                return $value;
            default:
                return $value;
        }
    }

    public function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }
        
        if (empty($this->fillable)) {
            return true;
        }
        
        return in_array($key, $this->fillable);
    }

    public function toArray(): array
    {
        $attributes = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }
        
        return $attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function save(): bool
    {
        if ($this->useTimestamps) {
            $this->updateTimestamps();
        }
        
        if ($this->exists) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }

    protected function performInsert(): bool
    {
        $query = $this->newQuery();
        
        $attributes = $this->getAttributesForInsert();
        
        if (empty($attributes)) {
            return false;
        }
        
        DatabaseManager::beginTransaction();
        
        try {
            $result = $query->insert($attributes);
            
            if ($result && $this->primaryKey !== null) {
                $lastInsertId = DatabaseManager::getConnection()->lastInsertId();
                if ($lastInsertId) {
                    $this->setAttribute($this->primaryKey, $lastInsertId);
                }
            }
            
            $this->exists = true;
            $this->syncOriginal();
            
            DatabaseManager::commit();
            return $result;
        } catch (\Exception $e) {
            DatabaseManager::rollback();
            throw new RuntimeException("Insert failed: " . $e->getMessage());
        }
    }

    protected function performUpdate(): bool
    {
        $query = $this->newQuery();
        
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true; // Nothing to update
        }
        
        if (!$this->getAttribute($this->primaryKey)) {
            throw new RuntimeException("Cannot update model without primary key");
        }
        
        DatabaseManager::beginTransaction();
        
        try {
            $affected = $query
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->update($dirty);
            
            $this->syncOriginal();
            
            DatabaseManager::commit();
            return $affected > 0;
        } catch (\Exception $e) {
            DatabaseManager::rollback();
            throw new RuntimeException("Update failed: " . $e->getMessage());
        }
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        if (!$this->getAttribute($this->primaryKey)) {
            throw new RuntimeException("Cannot delete model without primary key");
        }
        
        DatabaseManager::beginTransaction();
        
        try {
            $affected = $this->newQuery()
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->delete();
            
            $this->exists = false;
            
            DatabaseManager::commit();
            return $affected > 0;
        } catch (\Exception $e) {
            DatabaseManager::rollback();
            throw new RuntimeException("Delete failed: " . $e->getMessage());
        }
    }

    public static function find($id): ?static
    {
        return static::newQuery()->find($id);
    }

    public static function findOrFail($id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new RuntimeException("Model not found with ID: {$id}");
        }
        
        return $model;
    }

    public static function all(): array
    {
        $results = static::newQuery()->get();
        return array_map(function($attributes) {
            return static::newInstance($attributes, true);
        }, $results);
    }

    public static function where(string $column, string $operator, $value = null): QueryBuilder
    {
        return static::newQuery()->where($column, $operator, $value);
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $model = static::where(array_keys($attributes)[0], array_values($attributes)[0])->first();
        
        if ($model) {
            $model->fill($values);
            $model->save();
        } else {
            $model = static::create(array_merge($attributes, $values));
        }
        
        return $model;
    }

    public static function destroy($ids): int
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        $count = 0;
        foreach ($ids as $id) {
            $model = static::find($id);
            if ($model && $model->delete()) {
                $count++;
            }
        }
        
        return $count;
    }

    protected function updateTimestamps(): void
    {
        $time = date('Y-m-d H:i:s');
        
        if (!$this->exists && in_array('created_at', $this->timestamps)) {
            $this->setAttribute('created_at', $time);
        }
        
        if (in_array('updated_at', $this->timestamps)) {
            $this->setAttribute('updated_at', $time);
        }
    }

    protected function getAttributesForInsert(): array
    {
        $attributes = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey || $value !== null) {
                $attributes[$key] = $value;
            }
        }
        
        return $attributes;
    }

    protected function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    public static function newQuery(): QueryBuilder
    {
        $instance = new static();
        return (new QueryBuilder())->table($instance->getTable());
    }

    protected function newQuery(): QueryBuilder
    {
        return static::newQuery();
    }

    protected function getTable(): string
    {
        if (empty($this->table)) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
        
        return $this->table;
    }

    public static function newInstance(array $attributes = [], bool $exists = false): static
    {
        $model = new static($attributes);
        $model->exists = $exists;
        $model->syncOriginal();
        return $model;
    }

    public function fresh(): ?static
    {
        if (!$this->exists) {
            return null;
        }
        
        return static::find($this->getAttribute($this->primaryKey));
    }

    public function refresh(): static
    {
        if (!$this->exists) {
            return $this;
        }
        
        $fresh = $this->fresh();
        if ($fresh) {
            $this->attributes = $fresh->attributes;
            $this->syncOriginal();
        }
        
        return $this;
    }

    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }
}
