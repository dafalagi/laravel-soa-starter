<?php

namespace Modules\Auth\Tests\Unit\Services\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\RegisterService;
use Tests\TestCase;

class RegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegisterService $register_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->register_service = new RegisterService();
    }

    public function test_can_register_new_user(): void
    {
        // Given valid registration data
        $dto = new RegisterRequestDTO(
            'John Doe',
            'john@example.com',
            'password123',
            'password123'
        );

        // When registering the user
        $response = $this->register_service->execute($dto);

        // Then user should be created and response returned
        $this->assertDatabaseHas('auth_users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $this->assertEquals('John Doe', $response->user->name);
        $this->assertEquals('john@example.com', $response->user->email);
    }

    public function test_cannot_register_with_existing_email(): void
    {
        // Given an existing user
        User::factory()->create(['email' => 'john@example.com']);

        // When trying to register with same email
        $dto = new RegisterRequestDTO(
            'John Doe',
            'john@example.com',
            'password123',
            'password123'
        );

        // Then validation exception should be thrown
        $this->expectException(ValidationException::class);
        $this->register_service->execute($dto);
    }

    public function test_cannot_register_with_mismatched_passwords(): void
    {
        // When trying to register with mismatched passwords
        $dto = new RegisterRequestDTO(
            'John Doe',
            'john@example.com',
            'password123',
            'different_password'
        );

        // Then validation exception should be thrown
        $this->expectException(ValidationException::class);
        $this->register_service->execute($dto);
    }
}