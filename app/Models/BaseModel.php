<?php

namespace App\Models;

use App\Traits\HasModularFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\DTOs\User\Requests\UserRequestDTO;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;

class BaseModel extends Model
{
    use HasModularFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d';
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $hidden = [
        'id',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:U',
            'updated_at' => 'datetime:U',
            'deleted_at' => 'datetime:U',
        ];
    }

    public function createdBy(?array $dto): array
    {
        $get_user_service = app(GetUserServiceInterface::class);
        $request = UserRequestDTO::fromArray(array_merge($dto ?? [], [
            'user_uuid' => $this->created_by,
        ]));

        return $get_user_service->execute($request)['data'];
    }

    public function updatedBy(?array $dto): array
    {
        $get_user_service = app(GetUserServiceInterface::class);
        $request = UserRequestDTO::fromArray(array_merge($dto ?? [], [
            'user_uuid' => $this->updated_by,
        ]));

        return $get_user_service->execute($request)['data'];
    }

    public function deletedBy(?array $dto): array
    {
        $get_user_service = app(GetUserServiceInterface::class);
        $request = UserRequestDTO::fromArray(array_merge($dto ?? [], [
            'user_uuid' => $this->deleted_by,
        ]));

        return $get_user_service->execute($request)['data'];
    }
}
