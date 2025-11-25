<?php

namespace Modules\Auth\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Contracts\AuthServiceInterface;

class AuthService implements AuthServiceInterface
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequestDTO $dto): AuthResponseDTO
    {
        // Check if user already exists
        if (User::where('email', $dto->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        // Validate password confirmation
        if ($dto->password !== $dto->password_confirmation) {
            throw ValidationException::withMessages([
                'password' => ['The password confirmation does not match.'],
            ]);
        }

        // Create user
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        $user_dto = UserResponseDTO::fromModel($user);

        return AuthResponseDTO::fromUserAndToken($user_dto);
    }

    /**
     * Login user with credentials.
     */
    public function login(LoginRequestDTO $dto): AuthResponseDTO
    {
        if (!Auth::attempt(
            ['email' => $dto->email, 'password' => $dto->password],
            $dto->remember
        )) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $user_dto = UserResponseDTO::fromModel($user);

        return AuthResponseDTO::fromUserAndToken($user_dto);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Get the authenticated user.
     */
    public function user(): ?UserResponseDTO
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return UserResponseDTO::fromModel($user);
    }

    /**
     * Refresh the authentication token.
     */
    public function refresh(): AuthResponseDTO
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $user_dto = UserResponseDTO::fromModel($user);

        return AuthResponseDTO::fromUserAndToken($user_dto);
    }
}