<?php

namespace Modules\Auth\Services\User;

use App\Rules\ExistsId;
use App\Rules\ExistsUuid;
use App\Services\BaseService;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\Contracts\DeleteUserServiceInterface;

class DeleteUserService extends BaseService implements DeleteUserServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(mixed $dto): void
    {
        $dto = $this->prepare($dto);

        $model = User::find($dto['user_id']);

        $this->validateVersion($model, $dto['version']);
        $this->prepareAuditDelete($model);
        $model->save();

        $this->results['data'] = null;
        $this->results['message'] = __('auth::user.delete.success');
    }

    private function prepare(array $dto): array
    {
        if (!empty($dto['user_uuid']))
            $dto['user_id'] = $this->findIdByUuid(User::query(), $dto['user_uuid']);
        
        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            'user_id' => ['nullable', new ExistsId(new User())],
            'user_uuid' => ['nullable', 'required_without:user_id', new ExistsUuid(new User())],

            'version' => ['required', 'integer'],
        ];
    }
}
