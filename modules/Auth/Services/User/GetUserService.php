<?php

namespace Modules\Auth\Services\User;

use App\Traits\Pagination;
use Modules\Auth\DTOs\UserResponseDTO;
use Modules\Auth\Models\User;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;

class GetUserService implements GetUserServiceInterface
{
    use Pagination;

    /**
     * Get the authenticated user.
     */
    public function execute(array $dto): array
    {
        $dto = $this->prepare($dto);

        $model = User::orderBy($dto['sort_by'], $dto['sort_type']);

        if (isset($dto['with'])) {
            $model->with($dto['with']);
        }

        if (!empty($dto['user_uuid'])) {
            $model->where('uuid', $dto['user_uuid']);
            $data = $model->first();
        } else {
            if (isset($dto['with_pagination'])) {
                $results['pagination'] = $this->paginationDetail($dto['per_page'], $dto['page'], $model->count());
                $model = $this->paginateData($model, $dto['per_page'], $dto['page']);
            }

            $data = $model->get();
        }

        return [
            'message' => 'User successfully fetched.',
            'data' => UserResponseDTO::fromModel($data)->toArray(),
            'pagination' => $results['pagination'] ?? null,
        ];
    }

    /**
     * Prepare the get user operation.
     */
    private function prepare(array $dto): array
    {
        $dto['per_page'] = $dto['per_page'] ?? 10;
        $dto['page'] = $dto['page'] ?? 1;
        $dto['sort_by'] = $dto['sort_by'] ?? 'updated_at';
        $dto['sort_type'] = $dto['sort_type'] ?? 'desc';

        return $dto;
    }
}
