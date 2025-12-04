<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\User\Requests\UpdateUserRequestDTO;

interface UpdateUserServiceInterface
{
    /**
     * Execute the UpdateUserService operation.
     */
    public function execute(UpdateUserRequestDTO $dto, bool $sub_service = false): array;
}
