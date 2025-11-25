<?php

namespace Modules\Auth\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuthService;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $auth_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth_service = new AuthService();
    }

    public function test_register_creates_new_user(): void
    {
        $dto = new RegisterRequestDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
            password_confirmation: 'password123'
        );

        $response = $this->auth_service->register($dto);

        $this->assertDatabaseHas('auth_users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals('John Doe', $response->user->name);
        $this->assertEquals('john@example.com', $response->user->email);
    }

    public function test_register_throws_exception_for_existing_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $dto = new RegisterRequestDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
            password_confirmation: 'password123'
        );

        $this->expectException(ValidationException::class);
        $this->auth_service->register($dto);
    }

    public function test_register_throws_exception_for_password_mismatch(): void
    {
        $dto = new RegisterRequestDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
            password_confirmation: 'different_password'
        );

        $this->expectException(ValidationException::class);
        $this->auth_service->register($dto);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dto = new LoginRequestDTO(
            email: 'john@example.com',
            password: 'password123'
        );

        $response = $this->auth_service->login($dto);

        $this->assertEquals($user->id, $response->user->id);
        $this->assertEquals($user->email, $response->user->email);
    }

    public function test_login_throws_exception_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dto = new LoginRequestDTO(
            email: 'john@example.com',
            password: 'wrongpassword'
        );

        $this->expectException(ValidationException::class);
        $this->auth_service->login($dto);
    }

    public function test_user_returns_null_when_not_authenticated(): void
    {
        $user = $this->auth_service->user();
        $this->assertNull($user);
    }

    public function test_user_returns_dto_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user_dto = $this->auth_service->user();

        $this->assertNotNull($user_dto);
        $this->assertEquals($user->id, $user_dto->id);
        $this->assertEquals($user->email, $user_dto->email);
    }
}