<?php

return [
    'default' => $_ENV['CACHE_DRIVER'] ?? 'file',
    
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => $_ENV['CACHE_PATH'] ?? storage_path('cache'),
            'ttl' => (int) ($_ENV['CACHE_TTL'] ?? 3600),
        ],
        
        'array' => [
            'driver' => 'array',
            'ttl' => (int) ($_ENV['CACHE_TTL'] ?? 3600),
        ],
        
        'redis' => [
            'driver' => 'redis',
            'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => (int) ($_ENV['REDIS_DB'] ?? 0),
            'ttl' => (int) ($_ENV['CACHE_TTL'] ?? 3600),
        ],
    ],
    
    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'apileon_cache',
    
    // Cache performance settings
    'serialize' => true,
    'compress' => false,
    
    // Cleanup settings
    'cleanup_probability' => 2, // 2% chance of cleanup on cache operations
    'max_expired_items' => 1000, // Maximum expired items before forced cleanup
];

// Helper function for storage path
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string {
        $basePath = dirname(__DIR__);
        $storagePath = $basePath . '/storage';
        
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        return $storagePath . ($path ? '/' . ltrim($path, '/') : '');
    }
}
