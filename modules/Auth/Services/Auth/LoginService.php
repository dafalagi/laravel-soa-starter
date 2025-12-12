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

        /** @var \Modules\Auth\Models\User $user */
        $user = User::where('email', $dto['email'])
            ->where('is_active', true)
            ->first();

        if (!$user)
            throw new \Exception(__('auth::auth.login.invalid_credentials'), 401);

        if (Hash::check($dto['password'], $user->password) == false)
            throw new \Exception(__('auth::auth.login.invalid_credentials'), 401);
        
        $token = $user->createToken("{$dto['client']}_token");

        if ($dto['remember']) {
            $current_token = $user->tokens()->where('id', $token->token->id)->first();
            $current_token->expires_at = now()->addMonth();
            $current_token->save();
        }

        $access_token = $token->accessToken;
        $user = UserResponseDTO::fromModel($user);

        $this->results['message'] = __('auth::auth.login.success');
        $this->results['data'] = AuthResponseDTO::fromUserAndToken($user, $access_token)->toArray();
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
