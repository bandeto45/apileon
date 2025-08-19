<?php

namespace Apileon\Events;

// Database Events
class QueryExecuted extends Event
{
    public function __construct(string $sql, array $bindings, float $time)
    {
        parent::__construct([
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time
        ]);
    }
}

class ModelCreated extends Event
{
    public function __construct(string $model, array $attributes)
    {
        parent::__construct([
            'model' => $model,
            'attributes' => $attributes
        ]);
    }
}

class ModelUpdated extends Event
{
    public function __construct(string $model, array $attributes, array $original)
    {
        parent::__construct([
            'model' => $model,
            'attributes' => $attributes,
            'original' => $original
        ]);
    }
}

class ModelDeleted extends Event
{
    public function __construct(string $model, array $attributes)
    {
        parent::__construct([
            'model' => $model,
            'attributes' => $attributes
        ]);
    }
}

// HTTP Events
class RequestReceived extends Event
{
    public function __construct(string $method, string $uri, array $headers)
    {
        parent::__construct([
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers
        ]);
    }
}

class ResponseSent extends Event
{
    public function __construct(int $status, array $headers, $content)
    {
        parent::__construct([
            'status' => $status,
            'headers' => $headers,
            'content' => $content
        ]);
    }
}

// Cache Events
class CacheHit extends Event
{
    public function __construct(string $key, $value)
    {
        parent::__construct([
            'key' => $key,
            'value' => $value
        ]);
    }
}

class CacheMiss extends Event
{
    public function __construct(string $key)
    {
        parent::__construct([
            'key' => $key
        ]);
    }
}

class CacheWrite extends Event
{
    public function __construct(string $key, $value, int $ttl)
    {
        parent::__construct([
            'key' => $key,
            'value' => $value,
            'ttl' => $ttl
        ]);
    }
}
