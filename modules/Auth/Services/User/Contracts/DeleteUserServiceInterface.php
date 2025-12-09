<?php

namespace Modules\Auth\Services\User\Contracts;

use Modules\Auth\DTOs\User\Requests\DeleteUserRequestDTO;

interface DeleteUserServiceInterface
{
    /**
     * Execute the DeleteUserService operation.
     */
    public function execute(DeleteUserRequestDTO $dto, bool $sub_service = false): array;
}
