<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

trait Audit
{
    public function prepareAuditStore($object)
    {
        $object->{'uuid'} = generateUuid();
        $object->{'is_active'} = true;
        $object->{'version'} = 0;
        $object->{'created_by'}  =  Auth::user()->id ?? null;
        $object->{'updated_by'} =  Auth::user()->id ?? null;
        $object->{'created_at'} = time();
        $object->{'updated_at'} = time();
    }

    public function prepareAuditUpdate($object)
    {
        $object->{'version'} = $object->{'version'} + 1;
        $object->{'updated_by'} =  Auth::user()->id ?? null;
        $object->{'updated_at'} = time();
    }

    public function prepareAuditDelete($object)
    {
        $object->{'is_active'} = false;
        $object->{'deleted_by'} = Auth::user()->id ?? null;
        $object->{'deleted_at'} = time();
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
