<?php

namespace Modules\Auth\Services\Auth\Contracts;

use Modules\Auth\DTOs\AuthResponseDTO;

interface RefreshTokenServiceInterface
{
    /**
     * Refresh the authentication token.
     */
    public function execute(mixed $dto, bool $sub_service = false): array;
}
