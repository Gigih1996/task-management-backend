<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     * This is a mock authentication endpoint that returns a token for any valid credentials.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Mock authentication - In production, you would validate against a database
        // For this mock API, we accept any email/password that passes validation

        // Generate a mock token
        $token = base64_encode(Str::random(60));

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'email' => $validated['email'],
                    'name' => explode('@', $validated['email'])[0],
                ],
            ],
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ], 200);
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        // Mock user data based on authorization header
        $authHeader = $request->header('Authorization', '');

        if (empty($authHeader)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email' => 'user@example.com',
                'name' => 'Test User',
            ],
        ], 200);
    }
}
