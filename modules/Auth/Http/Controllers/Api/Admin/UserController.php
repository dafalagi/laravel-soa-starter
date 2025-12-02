<?php

namespace Modules\Auth\Http\Controllers\Api\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Auth\DTOs\User\Requests\StoreUserRequestDTO;
use Modules\Auth\DTOs\User\Requests\UserRequestDTO;
use Modules\Auth\Http\Requests\Api\Admin\User\StoreUserRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UserDetailRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UserListRequest;
use Modules\Auth\Http\Resources\Api\Admin\User\UserDetailResource;
use Modules\Auth\Http\Resources\Api\Admin\User\UserListResource;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;
use Modules\Auth\Services\User\Contracts\StoreUserServiceInterface;

class UserController extends ApiController
{
    public function __construct(
        private readonly GetUserServiceInterface $get_user_service,
        private readonly StoreUserServiceInterface $store_user_service,
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

    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = StoreUserRequestDTO::fromArray($request->all());
        $response = $this->store_user_service->execute($dto);

        return $this->response($response);
    }

    public function update($request): JsonResponse
    {
        return $this->response([]);
    }

    public function destroy($request): JsonResponse
    {
        return $this->response([]);
    }
}
