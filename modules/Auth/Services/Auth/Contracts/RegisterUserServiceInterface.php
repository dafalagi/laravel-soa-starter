<?php

namespace Modules\Auth\Services\Auth\Contracts;

use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\RegisterUserRequestDTO;

interface RegisterUserServiceInterface
{
    /**
     * Register a new user.
     */
    public function execute(RegisterUserRequestDTO $dto): AuthResponseDTO;
}
