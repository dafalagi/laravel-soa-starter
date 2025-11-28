<?php

namespace App\Services;

use App\Traits\Audit;
use App\Traits\Identifier;
use App\Traits\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BaseService
{
    use Audit;
    use Identifier;
    use Pagination;

    protected $results;

    protected function process(mixed $dto): void {}
    protected function rules(): array
    {
        return [];
    }

    public function execute(mixed $dto, bool $sub_service = false): array
    {
        $this->results = ['status_code' => 200, 'error' => null, 'message' => null, 'data' => null];

        if (!$sub_service) {
            DB::beginTransaction();

            try {
                $validator = Validator::make($dto, $this->rules());
                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }
                
                $this->process($dto);

                DB::commit();
            } catch (ValidationException $ex ) {
                DB::rollback();

                $this->results['status_code'] = 422;
                $this->results['errors'] = $ex->errors();
                $this->results['message'] = $ex->getMessage();
            } catch (\Exception $ex) {
                DB::rollback();

                $this->results['status_code'] = $ex->getCode() == 0 ? 500 : $ex->getCode() ;
                $original_status_code = $this->results['status_code'];

                if (strlen((string) $this->results['status_code']) > 3) {
                    $this->results['status_code'] = 500;
                }

                $this->results['error'] = $ex;

                if (env('APP_ENV') == 'local') {
                    $this->results['message'] = 'Caught exception: "' . $ex->getMessage() . '" on line ' . $ex->getLine() . ' of ' . $ex->getFile();

                    if (strlen((string) $original_status_code) > 3) {
                        $this->results['message'] .= ' (Original response code: ' . $original_status_code . ')';
                    }
                } else {
                    $this->results['message'] = $ex->getMessage();
                }
            }
        } else {
            $this->process($dto);
        }

        return $this->results;
    }
}
