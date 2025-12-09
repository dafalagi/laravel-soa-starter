<?php

namespace Modules\Auth\Tests\Unit\Services\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\User\Requests\StoreUserRequestDTO;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\StoreUserService;

class StoreUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private StoreUserService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new StoreUserService();
    }

    public function test_store_user_service_creates_user_successfully(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertInstanceOf(UserResponseDTO::class, $result['data']);

        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(__('auth::user.store.success'), $result['message']);
        
        $this->assertDatabaseHas('auth_users', [
            'email' => 'john@example.com'
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_store_user_service_validates_required_email(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'password' => 'password123',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_store_user_service_validates_email_format(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_store_user_service_validates_unique_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_store_user_service_validates_required_password(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_store_user_service_validates_password_minimum_length(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => '123', // Too short
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_store_user_service_hashes_password_if_needed(): void
    {
        // Arrange
        $plain_password = 'password123';
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => $plain_password,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotEquals($plain_password, $user->password);
        $this->assertTrue(Hash::check($plain_password, $user->password));
    }

    public function test_store_user_service_does_not_rehash_already_hashed_password(): void
    {
        // Arrange
        $hashed_password = Hash::make('password123');
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => $hashed_password,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals($hashed_password, $user->password);
    }

    public function test_store_user_service_returns_proper_response_structure(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        
        $this->assertInstanceOf(UserResponseDTO::class, $result['data']);
        $this->assertIsString($result['message']);
    }

    public function test_store_user_service_sets_audit_fields(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertNotNull($user->uuid);
        $this->assertEquals(0, $user->version);
    }

    public function test_store_user_service_creates_user_with_proper_table(): void
    {
        // Arrange
        $dto = StoreUserRequestDTO::fromArray([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $this->assertDatabaseHas('auth_users', [
            'email' => 'john@example.com'
        ]);
    }
}
