<?php

namespace Apileon\Cache;

use Apileon\Support\PerformanceMonitor;

class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private int $defaultTtl;

    public function __construct(int $defaultTtl = 3600)
    {
        $this->defaultTtl = $defaultTtl;
    }

    public function get(string $key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            PerformanceMonitor::incrementCounter('cache_misses');
            return $default;
        }

        $item = $this->cache[$key];
        
        if ($item['expires'] < time()) {
            unset($this->cache[$key]);
            PerformanceMonitor::incrementCounter('cache_misses');
            return $default;
        }

        PerformanceMonitor::incrementCounter('cache_hits');
        return $item['value'];
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        
        $this->cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    public function cleanup(): int
    {
        $cleaned = 0;
        $now = time();
        
        foreach ($this->cache as $key => $item) {
            if ($item['expires'] < $now) {
                unset($this->cache[$key]);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    public function getStats(): array
    {
        $total = count($this->cache);
        $expired = 0;
        $now = time();
        
        foreach ($this->cache as $item) {
            if ($item['expires'] < $now) {
                $expired++;
            }
        }
        
        return [
            'total_items' => $total,
            'expired_items' => $expired,
            'active_items' => $total - $expired
        ];
    }
}
