<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CityPut extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'state_id' => 'numeric|exists:states,id',
            'county_code' => 'numeric',
            'county' => 'string|max:150'
        ];
    }
}
