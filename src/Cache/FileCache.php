<?php

namespace Apileon\Cache;

use Apileon\Support\PerformanceMonitor;

class FileCache implements CacheInterface
{
    private string $cachePath;
    private int $defaultTtl;

    public function __construct(string $cachePath = null, int $defaultTtl = 3600)
    {
        $this->cachePath = $cachePath ?: sys_get_temp_dir() . '/apileon_cache';
        $this->defaultTtl = $defaultTtl;
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            PerformanceMonitor::incrementCounter('cache_misses');
            return $default;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        if ($data['expires'] < time()) {
            $this->delete($key);
            PerformanceMonitor::incrementCounter('cache_misses');
            return $default;
        }

        PerformanceMonitor::incrementCounter('cache_hits');
        return $data['value'];
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->getFilePath($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($file, serialize($data)) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
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

    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $dir = substr($hash, 0, 2);
        
        return $this->cachePath . '/' . $dir . '/' . $hash . '.cache';
    }

    public function cleanup(): int
    {
        $cleaned = 0;
        $files = glob($this->cachePath . '/*/*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = unserialize($content);
            
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
