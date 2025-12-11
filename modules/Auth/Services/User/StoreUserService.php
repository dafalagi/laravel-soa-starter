<?php

namespace Modules\Auth\Services\User;

use App\Rules\UniqueData;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\Contracts\StoreUserServiceInterface;

class StoreUserService extends BaseService implements StoreUserServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(mixed $dto): void
    {
        $dto = $this->prepare($dto);

        $model = new User();

        $model->email = $dto['email'];
        $model->password = $dto['password'];

        $this->prepareAuditStore($model);
        $model->save();

        $this->results['message'] = __('auth::user.store.success');
        $this->results['data'] = UserResponseDTO::fromModel($model)
            ->toArray([
                'uuid',
                'email',
                'email_verified_at',
                'version',
                'created_at',
                'createdBy',
            ]);
    }

    private function prepare(array $dto): array
    {
        if (isset($dto['password']) and Hash::needsRehash($dto['password']))
            $dto['password'] = Hash::make($dto['password']);

        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            'email' => ['required', 'email', new UniqueData(new User(), 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
