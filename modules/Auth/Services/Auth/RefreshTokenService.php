<?php

namespace Modules\Auth\Services\Auth;

use App\Services\BaseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\DTOs\AuthResponseDTO;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;

class RefreshTokenService extends BaseService implements RefreshTokenServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto, $sub_service);
    }

    protected function process(array $dto): void
    {
        $dto = $this->prepare($dto);

        /** @var \Modules\Auth\Models\User $user */
        $user = $dto['user'];
        
        $user->tokens()->each(function($token) {
            $token->revoke();
        });

        $token = $user->createToken(app('client').'_token')->accessToken;
        $user = UserResponseDTO::fromModel($user);

        $this->results['data'] = AuthResponseDTO::fromUserAndToken($user, $token);
        $this->results['message'] = __('auth::auth.token.refresh_success');
    }

    private function prepare(array $dto): array
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = Auth::user();
        $dto['user'] = $user;

        if (!$user->isActive())
            throw new AuthenticationException(__('auth::auth.token.inactive_account'));

        return $dto;
    }
}
