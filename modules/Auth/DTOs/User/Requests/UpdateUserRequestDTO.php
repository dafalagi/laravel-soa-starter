<?php

namespace Modules\Auth\DTOs\User\Requests;

class UpdateUserRequestDTO
{
    public function __construct(
        public readonly ?int $user_id,
        public readonly ?string $user_uuid,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?int $version,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: $data['user_id'] ?? null,
            user_uuid: $data['user_uuid'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'email' => $this->email,
            'password' => $this->password,
            'version' => $this->version,
        ];
    }
}
