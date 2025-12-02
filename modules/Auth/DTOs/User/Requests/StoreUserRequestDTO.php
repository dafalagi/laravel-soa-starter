<?php

namespace Modules\Auth\DTOs\User\Requests;

class StoreUserRequestDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $password_confirmation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            password_confirmation: $data['password_confirmation'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ];
    }
}