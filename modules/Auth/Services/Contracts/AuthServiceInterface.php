<?php

namespace Modules\Auth\Services\Contracts;

use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequestDTO $dto): AuthResponseDTO;

    /**
     * Login user with credentials.
     */
    public function login(LoginRequestDTO $dto): AuthResponseDTO;

    /**
     * Logout the authenticated user.
     */
    public function logout(): void;

    /**
     * Get the authenticated user.
     */
    public function user(): ?UserResponseDTO;

    /**
     * Refresh the authentication token.
     */
    public function refresh(): AuthResponseDTO;
}