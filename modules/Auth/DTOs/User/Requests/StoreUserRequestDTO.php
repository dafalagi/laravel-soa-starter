<?php

namespace Modules\Auth\DTOs\User\Requests;

class StoreUserRequestDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}