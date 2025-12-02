<?php

namespace Modules\Auth\Services\Auth;

use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;

class LogoutService extends BaseService implements LogoutServiceInterface
{
    public function __construct(
        protected RefreshTokenRepository $refreshTokenRepository
    ) {}

    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto, $sub_service);
    }

    protected function process(array $dto): void
    {
        $user = Auth::user();
        $this->revokeAccessAndRefreshToken($user);

        $this->results['data'] = null;
        $this->results['message'] = __('auth::auth.logout.success');
    }

    private function revokeAccessAndRefreshToken($user)
    {
        $user->tokens()->each(function($token) {
            $token->revoke();
            $token->delete();

            $this->refreshTokenRepository->revokeRefreshToken($token->id);
        });
    }
}
