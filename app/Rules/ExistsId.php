<?php

namespace App\Rules;

use App\Models\BaseModel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsId implements ValidationRule
{
    protected $table, $cols, $vals, $no_deleted_at;

    public function __construct($table, $cols = null, $vals = null, $no_deleted_at = false)
    {
        $this->table = $table;
        $this->cols = $cols;
        $this->vals = $vals;
        $this->no_deleted_at = $no_deleted_at;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->table instanceof \Illuminate\Database\Eloquent\Model and !$this->table instanceof BaseModel) {
            $this->table = DB::table($this->table);
        }

        if (strpos($value, ',') !== false) {
            $splitted_value = explode(',', $value);

            if (empty($value)) {
                $fail(__('validation.custom.id.is_empty'));
                return;
            }

            foreach ($splitted_value as $id) {
                if (!is_numeric($id)) {
                    $fail(__('validation.custom.id.not_valid'));
                    return;
                }
            }

            $query = $this->table->whereIn('id', $splitted_value);
        } else {
            if (empty($value)) {
                $fail(__('validation.custom.id.is_empty'));
                return;
            }

            if (!is_numeric($value)) {
                $fail(__('validation.custom.id.not_valid'));
                return;
            }

            $query = $this->table->where('id', $value);
        }

        $this->cols != null and $this->vals != null ? $query->where($this->cols, $this->vals) : '';
        $result = !empty($query->first()) ? true : false;

        if($result == false){
            $fail(__('validation.custom.id.not_exists'));
        }
    }
}
