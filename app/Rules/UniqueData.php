<?php

namespace App\Rules;

use App\Models\BaseModel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueData implements ValidationRule
{
    protected $identifier;

    protected $table;

    protected $column;

    public function __construct($table, $column, $identifier = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->identifier = $identifier;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->table instanceof \Illuminate\Database\Eloquent\Model and !$this->table instanceof BaseModel)
            throw new \Exception('Table must be an instance of Eloquent Model or BaseModel');

        $query = $this->table->where($this->column, $value);
        
        if(Str::isUuid($this->identifier)) {
            $this->identifier != null ? $query->where('uuid', '!=', $this->identifier) : null;
        }else {
            $this->identifier != null ? $query->where('id', '!=', $this->identifier) : null;
        }

        $result = empty($query->first()) ? true : false;
        
        if($result == false){
            $fail(__('validation.custom.unique_data', ['attribute' => $attribute]));
        }
    }
}
