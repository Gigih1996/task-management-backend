<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * Test successful login with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => [
                        'email',
                        'name',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ]);
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with missing password.
     */
    public function test_login_fails_with_missing_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login fails with short password.
     */
    public function test_login_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test logout endpoint.
     */
    public function test_logout_returns_success(): void
    {
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer mock-token-12345',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test me endpoint with valid token.
     */
    public function test_me_endpoint_with_valid_token(): void
    {
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer mock-token-12345',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'email',
                    'name',
                ],
            ]);
    }

    /**
     * Test me endpoint fails without token.
     */
    public function test_me_endpoint_fails_without_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }
}
