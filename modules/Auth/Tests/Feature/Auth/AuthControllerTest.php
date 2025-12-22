<?php

namespace Modules\Auth\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false; // Disable console output during tests

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a personal access client for Passport
        $this->artisan('passport:client --personal --name="Test Personal Access Client" --no-interaction');
    }

    public function test_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth::auth.login.success'),
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'uuid',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        // Verify user data in response
        $this->assertEquals($user->uuid, $response->json('data.user.uuid'));
        $this->assertEquals($user->email, $response->json('data.user.email'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_email(): void
    {
        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
            'remember' => false,
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => __('auth::auth.login.invalid_credentials'),
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'correctpassword',
            'is_active' => true,
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'remember' => false,
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => __('auth::auth.login.invalid_credentials'),
            ]);
    }

    public function test_login_fails_with_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => false, // Inactive user
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => __('auth::auth.login.invalid_credentials'),
            ]);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v0/admin/auth/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validates_email_format(): void
    {
        $payload = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'remember' => false,
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_accepts_valid_client_types(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        // Admin client is automatically set by LoginRequest
        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth::auth.login.success'),
            ]);
    }

    public function test_login_with_remember_flag(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ];

        $response = $this->postJson('/api/v0/admin/auth/login', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth::auth.login.success'),
            ]);

        // Verify token expiration is extended
        $user->refresh();
        $token = $user->tokens()->latest()->first();
        $this->assertEquals(now()->addMonth()->format('Y-m-d'), $token->expires_at->format('Y-m-d'));
    }

    public function test_can_logout_successfully(): void
    {
        [$user, $token] = $this->actingAsAuthenticated();

        // Verify user has tokens before logout
        $this->assertGreaterThan(0, $user->tokens()->count());

        $response = $this->withToken($token)
            ->postJson('/api/v0/admin/auth/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth::auth.logout.success'),
            ]);

        // Verify tokens are revoked after logout
        $user->refresh();
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v0/admin/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_logout_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token')
            ->postJson('/api/v0/admin/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_can_refresh_token_successfully(): void
    {
        [$user, $token] = $this->actingAsAuthenticated();

        $response = $this->withToken($token)
            ->postJson('/api/v0/admin/auth/refresh');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'uuid',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        // Verify user data in response
        $this->assertEquals($user->uuid, $response->json('data.user.uuid'));
        $this->assertEquals($user->email, $response->json('data.user.email'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));

        // Verify new token is different from old one
        $this->assertNotEquals($token, $response->json('data.token'));
    }

    public function test_refresh_requires_authentication(): void
    {
        $response = $this->postJson('/api/v0/admin/auth/refresh');

        $response->assertUnauthorized();
    }

    public function test_refresh_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token')
            ->postJson('/api/v0/admin/auth/refresh');

        $response->assertUnauthorized();
    }

    public function test_refresh_with_revoked_token(): void
    {
        [$user, $token] = $this->actingAsAuthenticated();

        // Revoke all user tokens
        $user->tokens()->each(function($token) {
            $token->revoke();
        });

        $response = $this->withToken($token)
            ->postJson('/api/v0/admin/auth/refresh');

        $response->assertUnauthorized();
    }

    private function actingAsAuthenticated(): array
    {
        $user = User::factory()->create([
            'email' => 'auth@example.com',
            'is_active' => true,
        ]);
        
        // Create actual token for Bearer header testing
        $token = $user->createToken('admin_token')->accessToken;

        return [$user, $token];
    }
}