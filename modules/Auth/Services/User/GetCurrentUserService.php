<?php

namespace Modules\Auth\Services\User;

use Illuminate\Support\Facades\Auth;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Services\User\Contracts\GetCurrentUserServiceInterface;

class GetCurrentUserService implements GetCurrentUserServiceInterface
{
    /**
     * Get the authenticated user.
     */
    public function execute(): ?UserResponseDTO
    {
        $this->prepare();

        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return UserResponseDTO::fromModel($user);
    }

    /**
     * Prepare the get current user operation.
     */
    private function prepare(): void
    {
        // Additional business logic validation can be added here
        // For example, checking user permissions, loading additional data, etc.
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Example: Check if user account is still valid
            if ($user && !$user->isActive()) {
                Auth::logout();
            }
        }
    }
}
