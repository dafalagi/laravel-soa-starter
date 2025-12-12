<?php

namespace Modules\Auth\Tests\Unit\Services\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;
use Modules\Auth\DTOs\User\Requests\DeleteUserRequestDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\DeleteUserService;

class DeleteUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private DeleteUserService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new DeleteUserService();
    }

    public function test_deletes_user_successfully_with_user_id(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto, true);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertNull($result['data']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(__('auth::user.delete.success'), $result['message']);
        
        // Verify soft delete
        $this->assertSoftDeleted('auth_users', [
            'id' => $user->id,
        ]);

        $deleted_user = User::withTrashed()->find($user->id);
        $this->assertNotNull($deleted_user->deleted_at);
        $this->assertEquals(0, $deleted_user->version);
    }

    public function test_deletes_user_successfully_with_user_uuid(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = DeleteUserRequestDTO::fromArray([
            'user_uuid' => $user->uuid,
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertNull($result['data']);
        
        // Verify soft delete
        $this->assertSoftDeleted('auth_users', [
            'uuid' => $user->uuid,
        ]);

        $deleted_user = User::withTrashed()->find($user->id);
        $this->assertNotNull($deleted_user->deleted_at);
        $this->assertEquals(0, $deleted_user->version);
    }

    public function test_validates_user_id_exists(): void
    {
        // Arrange
        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => 99999,
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_user_uuid_exists(): void
    {
        // Arrange
        $dto = DeleteUserRequestDTO::fromArray([
            'user_uuid' => 'non-existent-uuid',
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

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_validates_version_mismatch(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 5]);

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 3, // Wrong version
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->execute($dto, true);
    }

    public function test_sets_deleted_at(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $deleted_user = User::withTrashed()->find($user->id);
        $this->assertNotNull($deleted_user->deleted_at);
    }

    public function test_updates_audit_fields(): void
    {
        /** @var User */
        $logged_in_user = User::factory()->create();
        Passport::actingAs($logged_in_user);

        // Arrange
        $user = User::factory()->create(['version' => 0]);
        $deleted_at = $user->deleted_at;

        sleep(1); // Ensure timestamp difference

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $deleted_user = User::withTrashed()->find($user->id);
        $this->assertNotEquals($deleted_at, $deleted_user->deleted_at);
        $this->assertNotNull($deleted_user->deleted_by);
    }

    public function test_returns_proper_response_structure(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 0,
        ]);

        // Act
        $result = $this->service->execute($dto);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertNull($result['data']);
        $this->assertIsString($result['message']);
    }

    public function test_requires_user_id_or_uuid(): void
    {
        // Arrange
        $dto = DeleteUserRequestDTO::fromArray([
            'version' => 0,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->service->execute($dto, true);
    }

    public function test_preserves_other_fields(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'version' => 0,
        ]);
        $original_email = $user->email;
        $original_uuid = $user->uuid;
        $original_created_at = $user->created_at;

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user->id,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        $deleted_user = User::withTrashed()->find($user->id);
        $this->assertEquals($original_email, $deleted_user->email);
        $this->assertEquals($original_uuid, $deleted_user->uuid);
        $this->assertEquals($original_created_at, $deleted_user->created_at);
    }

    public function test_soft_deletes_not_hard_delete(): void
    {
        // Arrange
        $user = User::factory()->create(['version' => 0]);
        $user_id = $user->id;

        $dto = DeleteUserRequestDTO::fromArray([
            'user_id' => $user_id,
            'version' => 0,
        ]);

        // Act
        $this->service->execute($dto);

        // Assert
        // User should not be found in normal query
        $this->assertNull(User::find($user_id));
        
        // But should be found with trashed
        $this->assertNotNull(User::withTrashed()->find($user_id));
    }
}
