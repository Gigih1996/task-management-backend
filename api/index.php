<?php

// Laravel Serverless Entry Point for Vercel

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Set headers for CORS (before any output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Define Laravel start time
define('LARAVEL_START', microtime(true));

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Create HTTP Kernel
$kernel = $app->make(Kernel::class);

// Capture request
$request = Request::capture();

// Handle the request
$response = $kernel->handle($request);

// Send the response
$response->send();

// Terminate
$kernel->terminate($request, $response);
