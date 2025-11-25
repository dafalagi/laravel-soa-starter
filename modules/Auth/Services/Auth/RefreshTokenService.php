<?php

namespace Modules\Auth\Services\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;

class RefreshTokenService implements RefreshTokenServiceInterface
{
    /**
     * Refresh the authentication token.
     */
    public function execute(): AuthResponseDTO
    {
        $this->prepare();

        $user = Auth::user();
        $user_dto = UserResponseDTO::fromModel($user);

        return AuthResponseDTO::fromUserAndToken($user_dto);
    }

    /**
     * Prepare and validate the token refresh.
     */
    private function prepare(): void
    {
        // Validate that user is authenticated
        if (!Auth::check()) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /**
         * @var \Modules\Auth\Models\User|null $user
         */
        $user = Auth::user();
        
        // Additional business logic validation
        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        // Check if user account is still active (example validation)
        if (!$user->isActive()) {
            throw new AuthenticationException('User account is inactive.');
        }
    }
}
