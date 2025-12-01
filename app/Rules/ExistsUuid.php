<?php

namespace App\Rules;

use App\Models\BaseModel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsUuid implements ValidationRule
{
    protected $table, $cols, $vals;

    public function __construct($table, $cols = null, $vals = null)
    {
        $this->table = $table;
        $this->cols = $cols;
        $this->vals = $vals;
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

            $query = $this->table->whereIn('uuid', $splitted_value);
        } else {
            $query = $this->table->where('uuid', $value);
        }

        $this->cols != null and $this->vals != null ? $query->where($this->cols, $this->vals) : '';
        $result = !empty($query->first()) ? true : false;

        if(!$result)
            $fail(__('validation.custom.uuid.not_exists'));
    }
}
