<?php

// Laravel Serverless Entry Point for Vercel

use Illuminate\Http\Request;

// Define Laravel start time
define('LARAVEL_START', microtime(true));

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Handle the request
$response = $app->handleRequest(Request::capture());
