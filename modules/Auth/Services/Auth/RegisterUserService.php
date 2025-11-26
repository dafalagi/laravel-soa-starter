<?php

namespace Modules\Auth\Services\Auth;

use App\Traits\Audit;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\RegisterUserRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\Contracts\RegisterUserServiceInterface;

class RegisterUserService implements RegisterUserServiceInterface
{
    use Audit;

    /**
     * Register a new user.
     */
    public function execute(RegisterUserRequestDTO $dto): AuthResponseDTO
    {
        $dto = $this->prepare($dto->toArray());

        $model = new User();

        $model->name = $dto['name'];
        $model->email = $dto['email'];
        $model->password = Hash::make($dto['password']);

        $this->prepareAuditStore($model);
        $model->save();

        $user = UserResponseDTO::fromModel($model);

        return AuthResponseDTO::fromUserAndToken($user);
    }

    /**
     * Prepare and validate the registration data.
     */
    private function prepare(array $dto): array
    {
        $this->validateDto($dto);

        return $dto;
    }

    /**
     * Get validation rules for the DTO.
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:auth_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
