<?php

namespace Modules\Auth\DTOs\User\Requests;

class UserRequestDTO
{
    public function __construct(
        public readonly ?string $user_uuid = null,
        public readonly ?int $per_page = null,
        public readonly ?int $page = null,
        public readonly ?string $sort_by = null,
        public readonly ?string $sort_order = null,
        public readonly bool $with_pagination = false,
        public readonly ?array $with = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_uuid: $data['user_uuid'] ?? null,
            per_page: $data['per_page'] ?? null,
            page: $data['page'] ?? null,
            sort_by: $data['sort_by'] ?? null,
            sort_order: $data['sort_order'] ?? null,
            with_pagination: $data['with_pagination'] ?? false,
            with: $data['with'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_uuid' => $this->user_uuid,
            'per_page' => $this->per_page,
            'page' => $this->page,
            'sort_by' => $this->sort_by,
            'sort_order' => $this->sort_order,
            'with_pagination' => $this->with_pagination,
            'with' => $this->with,
        ];
    }
}