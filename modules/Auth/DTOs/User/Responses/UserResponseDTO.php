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
        public readonly ?Carbon $updated_at,
        public readonly ?User $createdBy,
        public readonly ?User $updatedBy,
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
            updated_at: $user->updated_at,
            createdBy: $user->createdBy,
            updatedBy: $user->updatedBy,
        );
    }

    public static function fromCollection(Collection $users): array
    {
        return array_map(fn(User $user) => (object) self::fromModel($user)->toArray(), $users->all());
    }

    /**
     * @param array<string>|null $only Only these fields will be included in the output array
     * @param array<string>|null $except These fields will be excluded from the output array
     */
    public function toArray(?array $only = null, ?array $except = null): array
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,

            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,

            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ];

        if ($only)
            $data = array_intersect_key($data, array_flip($only));

        if ($except)
            $data = array_diff_key($data, array_flip($except));

        return $data;
    }
}