<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\User\Requests\StoreUserRequestDTO;

interface StoreUserServiceInterface
{
    /**
     * Store a new user based on the provided data.
     */
    public function execute(StoreUserRequestDTO $dto, bool $sub_service = false): array;
}
