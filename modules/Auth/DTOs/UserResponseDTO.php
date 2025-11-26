<?php

namespace Modules\Auth\DTOs;

use Modules\Auth\Models\User;

class UserResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,

        public readonly string $name,
        public readonly string $email,
        public readonly ?string $email_verified_at,

        public readonly int $version,
        public readonly string $created_at,
        public readonly string $updated_at
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            uuid: $user->uuid,

            name: $user->name,
            email: $user->email,
            email_verified_at: $user->email_verified_at?->toISOString(),

            version: $user->version,
            created_at: $user->created_at->toISOString(),
            updated_at: $user->updated_at->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,

            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}