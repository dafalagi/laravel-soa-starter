<?php

namespace Modules\Auth\Tests\Unit\Services\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\User\Requests\UpdateUserRequestDTO;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\UpdateUserService;

class UpdateUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new UpdateUserService();
    }

    public function test_updates_user_successfully_with_user_id(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'version' => 0,
        ]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertArrayHasKey('data', $result);

        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(__('auth::user.update.success'), $result['message']);
        
        $this->assertDatabaseHas('auth_users', [
            'id' => $user->id,
            'email' => 'new@example.com',
            'version' => 1,
        ]);
    }

    public function test_updates_user_successfully_with_user_uuid(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'version' => 0,
        ]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_uuid' => $user->uuid,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertArrayHasKey('data', $result);
        
        $this->assertDatabaseHas('auth_users', [
            'uuid' => $user->uuid,
            'email' => 'new@example.com',
            'version' => 1,
        ]);
    }

    public function test_validates_user_id_exists(): void
    {
        // Arrange
        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => 99999,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_user_uuid_exists(): void
    {
        // Arrange
        $dto = UpdateUserRequestDTO::fromArray([
            'user_uuid' => 'non-existent-uuid',
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_email_format(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'invalid-email',
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_password_minimum_length(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'password' => '123',
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_required_version(): void
    {
        // Arrange
        $user = User::factory()->create();

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_version_mismatch(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 5]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 3, // Wrong version
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->execute($dto, true);
    }

    public function test_updates_email_only(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'version' => 0,
        ]);
        $original_password = $user->password;

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals('new@example.com', $updated_user->email);
        $this->assertEquals($original_password, $updated_user->password);
    }

    public function test_updates_password_only(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'version' => 0,
        ]);
        $original_email = $user->email;

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'password' => 'newpassword123',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals($original_email, $updated_user->email);
        $this->assertTrue(Hash::check('newpassword123', $updated_user->password));
    }

    public function test_updates_both_email_and_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'version' => 0,
        ]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'password' => 'newpassword123',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals('new@example.com', $updated_user->email);
        $this->assertTrue(Hash::check('newpassword123', $updated_user->password));
    }

    public function test_hashes_password_if_needed(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);
        $plain_password = 'newpassword123';

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'password' => $plain_password,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertNotEquals($plain_password, $updated_user->password);
        $this->assertTrue(Hash::check($plain_password, $updated_user->password));
    }

    public function test_does_not_rehash_already_hashed_password(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);
        $hashed_password = Hash::make('newpassword123');

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'password' => $hashed_password,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals($hashed_password, $updated_user->password);
    }

    public function test_increments_version(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals(1, $updated_user->version);
    }

    public function test_updates_audit_fields(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);
        $original_updated_at = $user->updated_at;

        sleep(1); // Ensure timestamp difference

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertNotEquals($original_updated_at, $updated_user->updated_at);
    }

    public function test_returns_proper_response_structure(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsString($result['message']);
    }

    public function test_requires_user_id_or_uuid(): void
    {
        // Arrange
        $dto = UpdateUserRequestDTO::fromArray([
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_preserves_unchanged_fields(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'version' => 0,
        ]);
        $original_uuid = $user->uuid;
        $original_created_at = $user->created_at;

        $dto = UpdateUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $updated_user = User::find($user->id);
        $this->assertEquals($original_uuid, $updated_user->uuid);
        $this->assertEquals($original_created_at, $updated_user->created_at);
    }
}
