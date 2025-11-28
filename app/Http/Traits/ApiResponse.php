<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a JSON response based on the status code.
     */
    protected function response(
        array $response,
    ): JsonResponse {
        $status_code = $response['status_code'] ?? 200;

        if ($status_code >= 200 && $status_code < 300) {
            return $this->successResponse(
                $response['message'] ?? 'Success',
                $response['data'] ?? null,
                $status_code
            );
        } else {
            return $this->errorResponse(
                $response['message'] ?? 'Error',
                $response['errors'] ?? null,
                $status_code
            );
        }
    }

    /**
     * Return a success JSON response.
     */
    protected function successResponse(
        string $message = 'Success',
        mixed $data = null,
        int $status_code = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status_code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $status_code = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status_code);
    }

    /**
     * Return a validation error JSON response.
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Return an unauthorized JSON response.
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Return a not found JSON response.
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, null, 404);
    }
}