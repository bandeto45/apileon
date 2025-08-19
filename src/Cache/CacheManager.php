<?php

namespace Apileon\Cache;

class CacheManager
{
    private static ?CacheInterface $instance = null;
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$instance = null; // Reset instance to use new config
    }

    public static function getInstance(): CacheInterface
    {
        if (self::$instance === null) {
            self::$instance = self::createCache();
        }

        return self::$instance;
    }

    private static function createCache(): CacheInterface
    {
        $driver = self::$config['driver'] ?? 'file';
        
        switch ($driver) {
            case 'file':
                return new FileCache(
                    self::$config['path'] ?? null,
                    self::$config['ttl'] ?? 3600
                );
            
            case 'array':
                return new ArrayCache(self::$config['ttl'] ?? 3600);
            
            default:
                throw new \InvalidArgumentException("Unsupported cache driver: {$driver}");
        }
    }

    // Convenience methods
    public static function get(string $key, $default = null)
    {
        return self::getInstance()->get($key, $default);
    }

    public static function set(string $key, $value, int $ttl = null): bool
    {
        return self::getInstance()->set($key, $value, $ttl);
    }

    public static function delete(string $key): bool
    {
        return self::getInstance()->delete($key);
    }

    public static function clear(): bool
    {
        return self::getInstance()->clear();
    }

    public static function remember(string $key, callable $callback, int $ttl = null)
    {
        return self::getInstance()->remember($key, $callback, $ttl);
    }
}
