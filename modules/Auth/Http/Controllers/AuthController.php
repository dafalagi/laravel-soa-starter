<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterUserRequestDTO;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RegisterUserServiceInterface;

class AuthController extends Controller
{
    use ApiResponse;
    
    public function __construct(
        private readonly RegisterUserServiceInterface $register_service,
        private readonly LoginServiceInterface $login_service,
        private readonly LogoutServiceInterface $logout_service,
        private readonly RefreshTokenServiceInterface $refresh_token_service,
    ) {}

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $dto = RegisterUserRequestDTO::fromArray($request->all());
            $response = $this->register_service->execute($dto);

            return $this->successResponse(
                'User registered successfully',
                $response->toArray(),
                201
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
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