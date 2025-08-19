<?php

require_once __DIR__ . '/../autoload.php';

use Apileon\Foundation\Application;

// Create the application instance
$app = new Application(__DIR__ . '/..');

// Run the application
$app->run();
