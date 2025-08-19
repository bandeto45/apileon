<?php

/**
 * Apileon Framework - Manual Autoloader
 * Use this when Composer is not available
 */

class ApileonAutoloader
{
    private static array $namespaces = [];
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        // Register the autoloader
        spl_autoload_register([self::class, 'autoload']);

        // Define namespace mappings
        self::addNamespace('Apileon\\', __DIR__ . '/src/');
        self::addNamespace('App\\', __DIR__ . '/app/');

        self::$registered = true;
    }

    public static function addNamespace(string $namespace, string $path): void
    {
        self::$namespaces[trim($namespace, '\\')] = rtrim($path, '/') . '/';
    }

    public static function autoload(string $className): void
    {
        // Normalize the class name
        $className = ltrim($className, '\\');

        // Find the namespace
        foreach (self::$namespaces as $namespace => $path) {
            if (strpos($className, $namespace) === 0) {
                // Remove namespace from class name
                $relativeClass = substr($className, strlen($namespace));
                
                // Convert namespace separators to directory separators
                $file = $path . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
                
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }
}

// Register the autoloader
ApileonAutoloader::register();

// Load helper functions
require_once __DIR__ . '/src/Support/functions.php';
