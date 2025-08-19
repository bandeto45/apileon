<?php

namespace Apileon\Foundation;

use Apileon\Http\Request;
use Apileon\Http\Response;
use Apileon\Routing\Router;
use Apileon\Routing\Route;

class Application
{
    private Router $router;
    private string $basePath;
    private array $config = [];
    private bool $booted = false;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->router = new Router();
        Route::setRouter($this->router);
        
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->registerMiddleware();
    }

    public function run(): void
    {
        if (!$this->booted) {
            $this->boot();
        }

        $request = new Request();
        $response = $this->router->dispatch($request);
        $response->send();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->loadRoutes();
        $this->booted = true;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function config(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    public function setConfig(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function environment(): string
    {
        return $this->config('app.env', 'production');
    }

    public function isDebug(): bool
    {
        return $this->config('app.debug', false);
    }

    private function loadEnvironment(): void
    {
        $envFile = $this->basePath('/.env');
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) {
                    continue;
                }
                
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^"(.*)"$/', $value, $matches)) {
                        $value = $matches[1];
                    } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                        $value = $matches[1];
                    }
                    
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    private function loadConfiguration(): void
    {
        $configPath = $this->basePath('/config');
        
        if (is_dir($configPath)) {
            $configFiles = glob($configPath . '/*.php');
            
            foreach ($configFiles as $file) {
                $key = basename($file, '.php');
                $this->config[$key] = require $file;
            }
        }

        // Set default configurations
        $this->config = array_merge([
            'app' => [
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'key' => $_ENV['APP_KEY'] ?? '',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
            ]
        ], $this->config);
    }

    private function loadRoutes(): void
    {
        $routesPath = $this->basePath('/routes');
        
        if (is_dir($routesPath)) {
            $routeFiles = glob($routesPath . '/*.php');
            
            foreach ($routeFiles as $file) {
                require $file;
            }
        }
    }

    private function registerMiddleware(): void
    {
        // Register built-in middleware
        $this->router->registerMiddleware('cors', \Apileon\Http\Middleware\CorsMiddleware::class);
        $this->router->registerMiddleware('auth', \Apileon\Http\Middleware\AuthMiddleware::class);
        $this->router->registerMiddleware('throttle', \Apileon\Http\Middleware\ThrottleMiddleware::class);
    }
}
