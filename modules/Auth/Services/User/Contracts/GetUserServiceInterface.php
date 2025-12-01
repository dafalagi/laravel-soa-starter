<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\User\Requests\UserRequestDTO;

interface GetUserServiceInterface
{
    /**
     * Get user(s) based on the provided criteria.
     */
    public function execute(UserRequestDTO $dto, bool $sub_service = false): array;
}
