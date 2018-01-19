<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'city_id' => 'required|numeric|exists:cities,id',
            'street' => 'required|string|max:100',
            'number' => 'required|numeric|size:30',
            'district' => 'required|string|max:100',
            'postcode' => 'required|numeric',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ];
    }
}
