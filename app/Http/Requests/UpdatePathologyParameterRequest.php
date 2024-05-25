<?php

namespace App\Http\Requests;

use App\Models\PathologyParameter;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePathologyParameterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = PathologyParameter::$rules;
        $rules['parameter_name'] = 'required|unique:pathology_parameters,parameter_name,'.$this->route('pathologyParameter')->id;

        return $rules;
    }
}
