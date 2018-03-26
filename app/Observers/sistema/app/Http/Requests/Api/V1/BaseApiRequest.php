<?php

namespace App\Http\Requests\Api\V1;


use App\Http\Responses\ApiResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

abstract class BaseApiRequest extends LaravelFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function validate()
    {
        $this->request->add(
            $this->json()->all()
        );

        parent::validate();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $exception = new ValidationException($validator);

        $message = $exception->getMessage();
        $errors  = $validator->getMessageBag()->toArray();

        throw new HttpResponseException(
            new ApiResponse(false, $message, $errors)
        );
    }
}