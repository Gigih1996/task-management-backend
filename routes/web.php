<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Task Management API',
        'version' => '1.0',
        'endpoints' => [
            'POST /api/login' => 'Login endpoint',
            'POST /api/logout' => 'Logout endpoint',
            'GET /api/me' => 'Get current user',
            'GET /api/tasks' => 'List tasks',
        ]
    ]);
});
