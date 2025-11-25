<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\UserResponseDTO;

interface GetCurrentUserServiceInterface
{
    /**
     * Get the authenticated user.
     */
    public function execute(): ?UserResponseDTO;
}
