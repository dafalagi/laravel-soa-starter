<?php

namespace Modules\Auth\Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false; // Disable console output during tests

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a personal access client for Passport
        $this->artisan('passport:client --personal --name="Test Personal Access Client" --no-interaction');
    }

    public function test_can_list_users(): void
    {
        [$admin, $token] = $this->actingAsAdmin();

        $users = User::factory(3)->create();

        $response = $this->withToken($token)
            ->getJson('/api/v0/admin/auth/users');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Ensure created users are present in the listing
        $listedEmails = collect($response->json('data'))->pluck('email');
        $users->each(fn (User $user) => $this->assertTrue($listedEmails->contains($user->email)));
        $this->assertTrue($listedEmails->contains($admin->email));
    }

    public function test_can_show_user_detail(): void
    {
        [$admin, $token] = $this->actingAsAdmin();

        $user = User::factory()->create();

        $response = $this->withToken($token)
            ->getJson("/api/v0/admin/auth/users/{$user->uuid}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_can_store_user(): void
    {
        [$admin, $token] = $this->actingAsAdmin();

        $payload = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->withToken($token)
            ->postJson('/api/v0/admin/auth/users', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => $payload['email'],
                    'version' => 0,
                ],
            ]);

        $this->assertDatabaseHas('auth_users', [
            'email' => $payload['email'],
            'created_by' => $admin->id,
        ]);
    }

    public function test_can_update_user(): void
    {
        [$admin, $token] = $this->actingAsAdmin();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'version' => 0,
        ]);

        $payload = [
            'email' => 'updated@example.com',
            'version' => $user->version,
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v0/admin/auth/users/{$user->uuid}", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => $payload['email'],
                    'version' => $user->version + 1,
                ],
            ]);

        $this->assertDatabaseHas('auth_users', [
            'uuid' => $user->uuid,
            'email' => $payload['email'],
            'version' => $user->version + 1,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_can_delete_user(): void
    {
        [$admin, $token] = $this->actingAsAdmin();

        $user = User::factory()->create([
            'version' => 0,
        ]);

        $payload = [
            'version' => $user->version,
        ];

        $response = $this->withToken($token)
            ->deleteJson("/api/v0/admin/auth/users/{$user->uuid}", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('auth_users', [
            'uuid' => $user->uuid,
            'is_active' => false,
        ]);

        $this->assertNotNull($user->fresh()->deleted_at);
    }

    private function actingAsAdmin(): array
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        
        // Create actual token for Bearer header testing
        $token = $admin->createToken('admin_token')->accessToken;

        return [$admin, $token];
    }
}
