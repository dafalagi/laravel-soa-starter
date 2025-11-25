<?php

namespace Modules\Auth\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;

class LogoutService implements LogoutServiceInterface
{
    /**
     * Logout the authenticated user.
     */
    public function execute(): void
    {
        $this->prepare();
        
        Auth::logout();
    }

    /**
     * Prepare the logout operation.
     */
    private function prepare(): void
    {
        // Additional business logic validation can be added here
        // For example, clearing specific user sessions, logging audit trails, etc.
        if (!Auth::check()) {
            // Already logged out, nothing to do
            return;
        }
    }
}
