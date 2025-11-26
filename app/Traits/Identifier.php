<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait Identifier
{
    public function findIdByUuid(Model $object, string $uuid): ?int
    {
        $results = $object->where('uuid', $uuid)->first();

        return $results->id;
    }

    public function findUuidById(Model $object, int $id): ?string
    {
        $results = $object->where('id', $id)->first();

        return $results->uuid;
    }
}
