<?php

namespace Modules\Auth\Services\User;

use App\Rules\ExistsUuid;
use App\Services\BaseService;
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;

class GetUserService extends BaseService implements GetUserServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(mixed $dto): void
    {
        $dto = $this->prepare($dto);

        $model = User::orderBy($dto['sort_by'], $dto['sort_type']);

        if (isset($dto['with'])) {
            $model->with($dto['with']);
        }

        if (!empty($dto['user_uuid']) || !empty($dto['user_id'])) {
            $user_id = $dto['user_id'] ?? $this->findIdByUuid(User::query(), $dto['user_uuid']);
            $model->where('id', $user_id);
            
            $data = (object) UserResponseDTO::fromModel($model->first())->toArray();
        } else {
            if ($dto['with_pagination'] === true) {
                $this->results['pagination'] = $this->paginationDetail($dto['per_page'], $dto['page'], $model->count());
                $model = $this->paginateData($model, $dto['per_page'], $dto['page']);
            }

            $data = UserResponseDTO::fromCollection($model->get());
        }

        $this->results['data'] = $data;
        $this->results['message'] = __('auth::user.get.success');
    }

    private function prepare(array $dto): array
    {
        $dto['per_page'] = $dto['per_page'] ?? 10;
        $dto['page'] = $dto['page'] ?? 1;
        $dto['sort_by'] = $dto['sort_by'] ?? 'updated_at';
        $dto['sort_type'] = $dto['sort_type'] ?? 'desc';

        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            'user_uuid' => ['nullable', new ExistsUuid(new User())],
        ];
    }
}
