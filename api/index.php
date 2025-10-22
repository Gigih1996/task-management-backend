<?php

// Laravel Serverless Entry Point for Vercel

use Illuminate\Http\Request;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set headers for CORS (before any output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Define Laravel start time
    define('LARAVEL_START', microtime(true));

    // Clear any cached route files in /tmp (Vercel serverless)
    $cacheFiles = [
        '/tmp/config.php',
        '/tmp/routes.php',
        '/tmp/events.php',
        '/tmp/packages.php',
        '/tmp/services.php'
    ];

    foreach ($cacheFiles as $cacheFile) {
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    // Load Composer autoloader
    require __DIR__ . '/../vendor/autoload.php';

    // Bootstrap Laravel application
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Handle the request (Laravel 11 style)
    $app->handleRequest(Request::capture());

} catch (\Throwable $e) {
    // Log error and return JSON response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Server Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
