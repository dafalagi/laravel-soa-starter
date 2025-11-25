<?php

namespace Modules\Auth\DTOs;

class AuthResponseDTO
{
    public function __construct(
        public readonly UserResponseDTO $user,
        public readonly ?string $token = null,
        public readonly ?string $tokenType = 'Bearer'
    ) {}

    public static function fromUserAndToken(UserResponseDTO $user, ?string $token = null): self
    {
        return new self(
            user: $user,
            token: $token,
            tokenType: $token ? 'Bearer' : null
        );
    }

    public function toArray(): array
    {
        $data = [
            'user' => $this->user->toArray(),
        ];

        if ($this->token) {
            $data['token'] = $this->token;
            $data['token_type'] = $this->tokenType;
        }

        return $data;
    }
}