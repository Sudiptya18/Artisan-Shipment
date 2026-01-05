<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
$autoloadPath = __DIR__.'/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Error: Composer autoload file not found. Please run "composer install".');
}
require $autoloadPath;

// Bootstrap Laravel and handle the request...
$bootstrapPath = __DIR__.'/../bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    die('Error: Bootstrap file not found.');
}
/** @var Application $app */
$app = require_once $bootstrapPath;

$app->handleRequest(Request::capture());
