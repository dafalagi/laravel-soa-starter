<?php

namespace Modules\Auth\Tests\Unit\Services\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\LoginService;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoginService $login_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->login_service = new LoginService();
    }

    public function test_can_login_with_valid_credentials(): void
    {
        // Given a user exists
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        // When logging in with valid credentials
        $dto = new LoginRequestDTO(
            'john@example.com',
            'password123',
            false
        );

        $response = $this->login_service->execute($dto);

        // Then user should be authenticated
        $this->assertEquals($user->id, Auth::id());
        $this->assertEquals('john@example.com', $response->user->email);
    }

    public function test_cannot_login_with_invalid_credentials(): void
    {
        // Given a user exists
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        // When logging in with invalid credentials
        $dto = new LoginRequestDTO(
            'john@example.com',
            'wrong_password',
            false
        );

        // Then validation exception should be thrown
        $this->expectException(ValidationException::class);
        $this->login_service->execute($dto);
        
        // And user should not be authenticated
        $this->assertFalse(Auth::check());
    }

    public function test_validates_required_fields(): void
    {
        // When trying to login with empty credentials
        $dto = new LoginRequestDTO(
            '',
            '',
            false
        );

        // Then validation exception should be thrown
        $this->expectException(ValidationException::class);
        $this->login_service->execute($dto);
    }
}