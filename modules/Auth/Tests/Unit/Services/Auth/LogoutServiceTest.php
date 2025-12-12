<?php

namespace Modules\Auth\Tests\Unit\Services\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\LogoutService;

class LogoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    private LogoutService $service;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a personal access client for Passport
        $this->artisan('passport:client --personal --name="Test Personal Access Client" --no-interaction');
        
        $this->service = $this->app->make(LogoutService::class);
    }

    public function test_revokes_user_tokens_successfully(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        // Create tokens for the user
        $user->createToken('admin_token');
        $user->createToken('web_token');
        
        // Authenticate the user
        Auth::login($user);
        
        // Verify tokens exist before logout
        $this->assertCount(2, $user->tokens);

        // Act
        $result = $this->service->execute([]);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertNull($result['data']);
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
        
        // Verify tokens are deleted after logout
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_handles_user_with_no_tokens(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        // Authenticate the user (no tokens created)
        Auth::login($user);
        
        // Verify no tokens exist
        $this->assertCount(0, $user->tokens);

        // Act
        $result = $this->service->execute([]);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertNull($result['data']);
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
    }

    public function test_revokes_multiple_tokens_of_same_type(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        // Create multiple tokens of the same type
        $user->createToken('admin_token');
        $user->createToken('admin_token');
        $user->createToken('admin_token');
        
        Auth::login($user);
        
        // Verify tokens exist
        $this->assertCount(3, $user->tokens);

        // Act
        $result = $this->service->execute([]);

        // Assert
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
        
        // Verify all tokens are deleted
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_revokes_tokens_for_different_clients(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        // Create tokens for different client types
        $user->createToken('admin_token');
        $user->createToken('web_token');
        $user->createToken('mobile_token');
        
        Auth::login($user);
        
        // Verify tokens exist
        $this->assertCount(3, $user->tokens);

        // Act
        $result = $this->service->execute([]);

        // Assert
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
        
        // Verify all client tokens are deleted
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_only_affects_authenticated_user_tokens(): void
    {
        // Arrange
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Create tokens for both users
        $user1->createToken('admin_token');
        $user2->createToken('admin_token');
        
        // Authenticate only user1
        Auth::login($user1);

        // Act - logout user1
        $result = $this->service->execute([]);

        // Assert
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
        
        // Verify only user1's tokens are affected
        $user1->refresh();
        $user2->refresh();
        
        $this->assertCount(0, $user1->tokens);
        $this->assertCount(1, $user2->tokens);
    }

    public function test_handles_already_revoked_tokens(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $token = $user->createToken('admin_token');
        
        // Manually revoke the token first
        $token->token->revoke();
        
        Auth::login($user);
        
        // Verify token still exists (but revoked)
        $this->assertCount(1, $user->tokens);

        // Act
        $result = $this->service->execute([]);

        // Assert - should still succeed
        $this->assertEquals(__('auth::auth.logout.success'), $result['message']);
        $this->assertNull($result['data']);
        
        // Verify token is deleted even if already revoked
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_response_structure(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $user->createToken('admin_token');
        Auth::login($user);

        // Act
        $result = $this->service->execute([]);

        // Assert - verify response structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('status_code', $result);
        
        $this->assertNull($result['data']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertIsString($result['message']);
    }
}
