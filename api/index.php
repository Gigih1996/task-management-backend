<?php

// Laravel Serverless Entry Point for Vercel

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
    // Load Composer autoloader
    require __DIR__ . '/../vendor/autoload.php';

    // Bootstrap Laravel application
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Handle the request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);

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
