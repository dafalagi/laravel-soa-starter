<?php

namespace Modules\Auth\DTOs\User\Requests;

class UserRequestDTO
{
    public function __construct(
        public readonly ?string $user_uuid,
        public readonly ?int $per_page,
        public readonly ?int $page,
        public readonly ?string $sort_by,
        public readonly ?string $sort_order,
        public readonly bool $with_pagination,
        public readonly ?array $with,
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