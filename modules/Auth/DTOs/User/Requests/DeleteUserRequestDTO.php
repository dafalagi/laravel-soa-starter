<?php

namespace Modules\Auth\DTOs\User\Requests;

class DeleteUserRequestDTO
{
    public function __construct(
        public readonly ?int $user_id,
        public readonly ?string $user_uuid,
        public readonly ?int $version,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: $data['user_id'] ?? null,
            user_uuid: $data['user_uuid'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'version' => $this->version,
        ];
    }
}
