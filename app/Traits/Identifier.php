<?php

namespace App\Traits;

trait Identifier
{
    public function findIdByUuid(mixed $object, string $uuid): ?int
    {
        $results = $object->where('uuid', $uuid)->first();

        return $results->id;
    }

    public function findUuidById(mixed $object, int $id): ?string
    {
        $results = $object->where('id', $id)->first();

        return $results->uuid;
    }
}
