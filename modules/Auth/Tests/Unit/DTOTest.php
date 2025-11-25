<?php

namespace Modules\Auth\Tests\Unit;

use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\Models\User;
use Tests\TestCase;

class DTOTest extends TestCase
{
    public function test_login_request_dto_from_array(): void
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'remember' => true,
        ];

        $dto = LoginRequestDTO::fromArray($data);

        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
        $this->assertTrue($dto->remember);
    }

    public function test_register_request_dto_from_array(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $dto = RegisterRequestDTO::fromArray($data);

        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
        $this->assertEquals('password123', $dto->password_confirmation);
    }

    public function test_user_response_dto_from_model(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dto = UserResponseDTO::fromModel($user);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
    }

    public function test_auth_response_dto_to_array(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userDto = UserResponseDTO::fromModel($user);
        $authDto = AuthResponseDTO::fromUserAndToken($userDto, 'test-token');

        $array = $authDto->toArray();

        $this->assertArrayHasKey('user', $array);
        $this->assertArrayHasKey('token', $array);
        $this->assertArrayHasKey('token_type', $array);
        $this->assertEquals('test-token', $array['token']);
        $this->assertEquals('Bearer', $array['token_type']);
    }
}