<?php

namespace Modules\Auth\Services\Auth\Contracts;

use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;

interface RegisterServiceInterface
{
    /**
     * Register a new user.
     */
    public function execute(RegisterRequestDTO $dto): AuthResponseDTO;
}
