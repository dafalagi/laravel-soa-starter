<?php

namespace Modules\Auth\Services\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\RegisterUserRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\Contracts\RegisterUserServiceInterface;

class RegisterUserService implements RegisterUserServiceInterface
{
    /**
     * Register a new user.
     */
    public function execute(RegisterUserRequestDTO $dto): AuthResponseDTO
    {
        $this->prepare($dto);

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
     * Prepare and validate the registration data.
     */
    private function prepare(RegisterUserRequestDTO $dto): void
    {
        // Validate input data
        $validator = Validator::make($dto->toArray(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:auth_users'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Additional business logic validation
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
    }
}
