<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RegisterServiceInterface;
use Modules\Auth\Services\User\Contracts\GetCurrentUserServiceInterface;

class AuthController extends Controller
{
    use ApiResponse;
    
    public function __construct(
        private readonly RegisterServiceInterface $register_service,
        private readonly LoginServiceInterface $login_service,
        private readonly LogoutServiceInterface $logout_service,
        private readonly RefreshTokenServiceInterface $refresh_token_service,
        private readonly GetCurrentUserServiceInterface $get_current_user_service
    ) {}

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $dto = RegisterRequestDTO::fromArray($request->all());
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
        try {
            $dto = LoginRequestDTO::fromArray($request->all());
            $response = $this->login_service->execute($dto);

            return $this->successResponse(
                'User logged in successfully',
                $response->toArray()
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Authentication failed',
                $e->errors(),
                401
            );
        }
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
     * Get authenticated user.
     */
    public function user(): JsonResponse
    {
        $user = $this->get_current_user_service->execute();

        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        return $this->successResponse(
            'User retrieved successfully',
            $user->toArray()
        );
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