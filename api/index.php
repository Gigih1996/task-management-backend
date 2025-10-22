<?php

// Laravel Serverless Entry Point for Vercel

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Set headers for CORS (before any output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Define Laravel start time
    define('LARAVEL_START', microtime(true));

    // Check if APP_KEY is set
    if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
        throw new Exception('APP_KEY environment variable is not set. Please configure it in Vercel Dashboard.');
    }

    // Load Composer autoloader
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception('Composer autoloader not found. Please run composer install.');
    }
    require __DIR__ . '/../vendor/autoload.php';

    // Bootstrap Laravel application
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Create custom exception handler that NEVER uses views
    $app->singleton(
        \Illuminate\Contracts\Debug\ExceptionHandler::class,
        function($app) {
            return new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
                public function render($request, Throwable $e) {
                    $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                    if ($status < 100 || $status >= 600) $status = 500;

                    // Use Response class directly - avoid response() helper that loads view service
                    return new \Illuminate\Http\JsonResponse([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Server Error',
                        'error' => class_basename($e),
                    ], $status);
                }

                protected function registerErrorViewPaths() {
                    // Override to do nothing - prevent view loading
                }
            };
        }
    );

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

} catch (Throwable $e) {
    // Error occurred during bootstrap or request handling
    http_response_code(500);

    echo json_encode([
        'error' => 'Server Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString()),
        'environment' => [
            'APP_KEY_SET' => !empty($_ENV['APP_KEY'] ?? getenv('APP_KEY')),
            'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?? 'not set',
            'APP_ENV' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'not set',
        ]
    ], JSON_PRETTY_PRINT);
}
