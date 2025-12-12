<?php

namespace Modules\Auth\Services\Auth;

use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\Auth\Responses\AuthResponseDTO;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;

class LoginService extends BaseService implements LoginServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(array $dto): void
    {
        $dto = $this->prepare($dto);

        $user = User::where('email', $dto['email'])
            ->where('is_active', true)
            ->first();

        if (!$user)
            throw new \Exception(__('auth::auth.login.invalid_credentials'), 401);

        if (Hash::check($dto['password'], $user->password) == false)
            throw new \Exception(__('auth::auth.login.invalid_credentials'), 401);

        $token = $user->createToken("{$dto['client']}_token")->accessToken;
        $user = UserResponseDTO::fromModel($user);

        $this->results['data'] = AuthResponseDTO::fromUserAndToken($user, $token)->toArray();
        $this->results['message'] = __('auth::auth.login.success');
    }

    private function prepare(array $dto): array
    {
        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['required', 'boolean'],
            'client' => ['required', 'string', 'in:admin,web,mobile'],
        ];
    }
}
