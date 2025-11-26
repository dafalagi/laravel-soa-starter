<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\UserResponseDTO;

interface GetUserServiceInterface
{
    /**
     * Get the authenticated user.
     */
    public function execute(array $dto): array;
}
