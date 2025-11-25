<?php

namespace Modules\Auth\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;

class LoginService implements LoginServiceInterface
{
    /**
     * Login user with credentials.
     */
    public function execute(LoginRequestDTO $dto): AuthResponseDTO
    {
        $this->prepare($dto);

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
     * Prepare and validate the login data.
     */
    private function prepare(LoginRequestDTO $dto): void
    {
        // Validate input data
        $validator = Validator::make($dto->toArray(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Additional business logic validation
        if (empty(trim($dto->email)) || empty(trim($dto->password))) {
            throw ValidationException::withMessages([
                'credentials' => ['Email and password are required.'],
            ]);
        }
    }
}
