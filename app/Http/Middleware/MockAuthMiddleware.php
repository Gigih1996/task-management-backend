<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MockAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Mock authentication middleware that checks for Bearer token in Authorization header.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        // Check if Authorization header is present
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid token.',
            ], 401);
        }

        // Extract token
        $token = substr($authHeader, 7);

        // Validate token is not empty
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is empty.',
            ], 401);
        }

        // In a mock API, we accept any non-empty token
        // In production, you would validate this token against the database
        return $next($request);
    }
}
