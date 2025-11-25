<?php

namespace Modules\Auth\Services\Auth\Contracts;

use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\LoginRequestDTO;

interface LoginServiceInterface
{
    /**
     * Login user with credentials.
     */
    public function execute(LoginRequestDTO $dto): AuthResponseDTO;
}
