<?php

namespace Modules\Auth\Services\Auth\Contracts;

interface LogoutServiceInterface
{
    /**
     * Logout the authenticated user.
     */
    public function execute(): void;
}
