<?php

namespace Modules\Auth\Http\Controllers\Api\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Auth\DTOs\User\Requests\DeleteUserRequestDTO;
use Modules\Auth\DTOs\User\Requests\StoreUserRequestDTO;
use Modules\Auth\DTOs\User\Requests\UpdateUserRequestDTO;
use Modules\Auth\DTOs\User\Requests\UserRequestDTO;
use Modules\Auth\Http\Requests\Api\Admin\User\DeleteUserRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\StoreUserRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UpdateUserRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UserDetailRequest;
use Modules\Auth\Http\Requests\Api\Admin\User\UserListRequest;
use Modules\Auth\Http\Resources\Api\Admin\User\UserDetailResource;
use Modules\Auth\Http\Resources\Api\Admin\User\UserListResource;
use Modules\Auth\Services\User\Contracts\DeleteUserServiceInterface;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;
use Modules\Auth\Services\User\Contracts\StoreUserServiceInterface;
use Modules\Auth\Services\User\Contracts\UpdateUserServiceInterface;

class UserController extends ApiController
{
    public function __construct(
        private readonly GetUserServiceInterface $get_service,
        private readonly StoreUserServiceInterface $store_service,
        private readonly UpdateUserServiceInterface $update_service,
        private readonly DeleteUserServiceInterface $delete_service,
    ) {}

    public function index(UserListRequest $request): JsonResponse
    {
        $dto = UserRequestDTO::fromArray($request->all());
        $response = $this->get_service->execute($dto);

        $response['data'] = is_array($response['data']) ? UserListResource::collection($response['data']) : 
            new UserListResource($response['data']);

    return $this->response($response);
    }

    public function show(UserDetailRequest $request): JsonResponse
    {
        $dto = UserRequestDTO::fromArray($request->all());
        $response = $this->get_service->execute($dto);

        $response['data'] = new UserDetailResource($response['data']);

        return $this->response($response);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $dto = StoreUserRequestDTO::fromArray($request->all());
        $response = $this->store_service->execute($dto);

        return $this->response($response);
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $dto = UpdateUserRequestDTO::fromArray($request->all());
        $response = $this->update_service->execute($dto);

        return $this->response($response);
    }

    public function destroy(DeleteUserRequest $request): JsonResponse
    {
        $dto = DeleteUserRequestDTO::fromArray($request->all());
        $response = $this->delete_service->execute($dto);

        return $this->response($response);
    }
}
