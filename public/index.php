<?php

// Try Composer autoloader first, fallback to manual autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../autoload.php';
}

use Apileon\Foundation\Application;

// Create the application instance
$app = new Application(__DIR__ . '/..');

// Run the application
$app->run();
