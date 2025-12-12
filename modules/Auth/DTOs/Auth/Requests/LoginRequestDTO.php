<?php

namespace Modules\Auth\DTOs\Auth\Requests;

class LoginRequestDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
        public readonly ?string $client = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            remember: $data['remember'] ?? false,
            client: $data['client'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
            'client' => $this->client,
        ];
    }
}