<?php

namespace Modules\Auth\Tests\Unit\Services\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\Auth\Requests\LoginRequestDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\LoginService;

class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    private LoginService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new LoginService();

        // Create a personal access client for Passport
        $this->artisan('passport:client --personal --name="Test Personal Access Client" --no-interaction');
    }

    public function test_login_service_authenticates_user_successfully(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act
        $result = $this->service->execute($dto, true);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(__('auth::auth.login.success'), $result['message']);
        
        // Verify response structure
        $data = $result['data'];
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_type', $data);
        
        // Verify user data
        $this->assertEquals($user->uuid, $data['user']['uuid']);
        $this->assertEquals($user->email, $data['user']['email']);
        
        // Verify token
        $this->assertNotEmpty($data['token']);
        $this->assertEquals('Bearer', $data['token_type']);
    }

    public function test_login_service_fails_with_invalid_email(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('auth::auth.login.invalid_credentials'));
        $this->expectExceptionCode(401);
        
        $this->service->execute($dto, true);
    }

    public function test_login_service_fails_with_invalid_password(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'correctpassword',
            'is_active' => true,
        ]);

        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('auth::auth.login.invalid_credentials'));
        $this->expectExceptionCode(401);
        
        $this->service->execute($dto, true);
    }

    public function test_login_service_fails_with_inactive_user(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => false, // Inactive user
        ]);

        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('auth::auth.login.invalid_credentials'));
        $this->expectExceptionCode(401);
        
        $this->service->execute($dto, true);
    }

    public function test_login_service_validates_required_email(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_login_service_validates_email_format(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'email' => 'invalid-email',
            'password' => 'password123',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_login_service_validates_required_password(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'remember' => false,
            'client' => 'admin',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_login_service_validates_required_client(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_login_service_validates_client_values(): void
    {
        // Arrange
        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'invalid_client',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_login_service_accepts_valid_client_types(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $valid_clients = ['admin', 'web', 'mobile'];

        foreach ($valid_clients as $client) {
            $dto = LoginRequestDTO::fromArray([
                'email' => 'test@example.com',
                'password' => 'password123',
                'remember' => false,
                'client' => $client,
            ]);

            // Act
            $result = $this->service->execute($dto, true);

            // Assert
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('token', $result['data']);
            
            // Verify token name contains client type
            $tokens = $user->tokens()->where('name', "{$client}_token")->get();
            $this->assertGreaterThan(0, $tokens->count());
        }
    }

    public function test_login_service_validates_remember_as_boolean(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
            'client' => 'admin',
        ]);

        // Act
        $result = $this->service->execute($dto, true);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(__('auth::auth.login.success'), $result['message']);
    }

    public function test_login_service_creates_token_with_correct_name(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $dto = LoginRequestDTO::fromArray([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => false,
            'client' => 'web',
        ]);

        // Act
        $this->service->execute($dto, true);

        // Assert
        $tokens = $user->tokens()->where('name', 'web_token')->get();
        $this->assertGreaterThan(0, $tokens->count());
    }
}
