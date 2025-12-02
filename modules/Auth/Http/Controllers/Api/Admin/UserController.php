<?php

namespace Modules\Auth\Http\Controllers\Api\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\DTOs\User\Requests\UserRequestDTO;
use Modules\Auth\Http\Requests\Api\Admin\User\UserDetailRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UserListRequest;
use Modules\Auth\Http\Resources\Api\Admin\User\UserDetailResource;
use Modules\Auth\Http\Resources\Api\Admin\User\UserListResource;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;

class UserController extends ApiController
{
    public function __construct(
        private readonly GetUserServiceInterface $get_user_service
    ) {}

    public function index(UserListRequest $request): JsonResponse
    {
        $dto = UserRequestDTO::fromArray($request->all());
        $response = $this->get_user_service->execute($dto);

        $response['data'] = is_array($response['data']) ? UserListResource::collection($response['data']) : 
            new UserListResource($response['data']);

        return $this->response($response);
    }

    public function show(UserDetailRequest $request): JsonResponse
    {
        $dto = UserRequestDTO::fromArray($request->all());
        $response = $this->get_user_service->execute($dto);

        $response['data'] = new UserDetailResource($response['data']);

        return $this->response($response);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->response([]);
    }
}
