<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\Http\Requests\Api\Admin\Auth\LoginRequest;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;

class AuthController extends Controller
{
    use ApiResponse;
    
    public function __construct(
        private readonly LoginServiceInterface $login_service,
        private readonly LogoutServiceInterface $logout_service,
        private readonly RefreshTokenServiceInterface $refresh_token_service,
    ) {}

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginRequestDTO::fromArray($request->all());
        $response = $this->login_service->execute($dto);

        return $this->response($response);
    }

    /**
     * Logout user.
     */
    public function logout(): JsonResponse
    {
        $this->logout_service->execute();

        return $this->successResponse('User logged out successfully');
    }

    /**
     * Refresh authentication.
     */
    public function refresh(): JsonResponse
    {
        try {
            $response = $this->refresh_token_service->execute();

            return $this->successResponse(
                'Token refreshed successfully',
                $response->toArray()
            );
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Token refresh failed');
        }
    }
}