<?php

namespace Modules\Auth\Http\Resources\Api\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'created_at' => $this->created_at->format('d M Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d M Y H:i:s'),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
        ];
    }
}
