<?php

namespace Modules\Auth\Http\Requests\Api\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class GetUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'user_uuid' => $this->route('user_uuid'),
        ]);
    }
}
