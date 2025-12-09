<?php

namespace Modules\Auth\Services\User;

use App\Rules\ExistsId;
use App\Rules\ExistsUuid;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\Contracts\UpdateUserServiceInterface;

class UpdateUserService extends BaseService implements UpdateUserServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(mixed $dto): void
    {
        $dto = $this->prepare($dto);

        $model = User::find($dto['user_id']);

        $model->email = $dto['email'] ?? $model->email;
        $model->password = $dto['password'] ?? $model->password;

        $this->validateVersion($model, $dto['version']);
        $this->prepareAuditUpdate($model);
        $model->save();

        $this->results['data'] = UserResponseDTO::fromModel($model);
        $this->results['message'] = __('auth::user.update.success');
    }

    private function prepare(array $dto): array
    {
        if (!empty($dto['user_uuid']))
            $dto['user_id'] = $this->findIdByUuid(User::query(), $dto['user_uuid']);

        if (isset($dto['password']) and Hash::needsRehash($dto['password']))
            $dto['password'] = Hash::make($dto['password']);
        
        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            'user_id' => ['nullable', new ExistsId(new User())],
            'user_uuid' => ['nullable', 'required_without:user_id', new ExistsUuid(new User())],

            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],

            'version' => ['required', 'integer'],
        ];
    }
}
