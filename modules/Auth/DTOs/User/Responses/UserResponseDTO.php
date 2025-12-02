<?php

namespace Modules\Auth\DTOs\User\Responses;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;

class UserResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,

        public readonly string $email,
        public readonly ?Carbon $email_verified_at,

        public readonly int $version,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            uuid: $user->uuid,

            email: $user->email,
            email_verified_at: $user->email_verified_at,

            version: $user->version,
            created_at: $user->created_at,
            updated_at: $user->updated_at
        );
    }

    public static function fromCollection(Collection $users): array
    {
        return array_map(fn(User $user) => self::fromModel($user), $users->all());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,

            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,

            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}