<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\RegisterRequestDTO;
use Modules\Auth\Services\Contracts\AuthServiceInterface;

class AuthController extends Controller
{
    use ApiResponse;
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ]);

        try {
            $dto = RegisterRequestDTO::fromArray($validated);
            $response = $this->authService->register($dto);

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
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        try {
            $dto = LoginRequestDTO::fromArray($validated);
            $response = $this->authService->login($dto);

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
        $this->authService->logout();

        return $this->successResponse('User logged out successfully');
    }

    /**
     * Get authenticated user.
     */
    public function user(): JsonResponse
    {
        $user = $this->authService->user();

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
            $response = $this->authService->refresh();

            return $this->successResponse(
                'Token refreshed successfully',
                $response->toArray()
            );
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Token refresh failed');
        }
    }
}