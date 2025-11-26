<?php

namespace Modules\Auth\Services\Auth;

use App\Traits\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;

class LoginService implements LoginServiceInterface
{
    use Audit;

    /**
     * Login user with credentials.
     */
    public function execute(LoginRequestDTO $dto): AuthResponseDTO
    {
        $dto = $this->prepare($dto->toArray());

        $user = User::where('email', $dto['email'])
            ->where('is_active', true)
            ->first();

        if(!$user)
            throw new \Exception("Invalid credentials", 401);

        if(Hash::check($dto['password'], $user->password) == false)
            throw new \Exception("Invalid credentials", 401);

        $token = $user->createToken("user_token")->accessToken;
        $user = UserResponseDTO::fromModel($user);

        return AuthResponseDTO::fromUserAndToken($user, $token);
    }

    /**
     * Prepare and validate the login data.
     */
    private function prepare(array $dto): array
    {
        $this->validateDto($dto);

        return $dto;
    }

    private function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }
}
