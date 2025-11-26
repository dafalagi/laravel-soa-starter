<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

trait Audit
{
    public function prepareAuditStore($object)
    {
        $object->{'uuid'} = generateUuid();
        $object->{'version'} = 0;
        $object->{'created_by'}  =  Auth::user()->id ?? null;
        $object->{'updated_by'} =  Auth::user()->id ?? null;
    }

    public function prepareAuditUpdate($object)
    {
        $object->{'version'} = $object->{'version'} + 1;
        $object->{'updated_by'} =  Auth::user()->id ?? null;
    }

    public function prepareAuditDelete($object)
    {
        $object->{'deleted_by'} = Auth::user()->id ?? null;
    }

    public function prepareAuditRestore($object)
    {
        $object->{'deleted_at'} = null;
        $object->{'deleted_by'} = null;
    }

    // Validate version for optimistic locking
    public function validateVersion($object, $request_version)
    {
        if ($object->{'version'} != $request_version)
            throw new \Exception("Version not match, please get the latest data and try again", 409);
    }

    public function validateDto($dto)
    {
        $validator = Validator::make($dto, $this->rules());
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    public function restrictSoftDeletes($object)
    {
        if (method_exists($object, 'getRestrictOnDeleteRelations')) {
            foreach ($object->getRestrictOnDeleteRelations() as $relation) {
                if ($object->$relation()->exists()) {
                    throw new \Exception('Cannot delete ' . class_basename($object) . '. Related ' . $relation . ' records exist.', 422);
                }
            }
        }
    }
}
