<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPut extends FormRequest
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
            'user_id' => 'numeric|exists:users,id',
            'address_id' => 'numeric|exists:addresses,id',
            'title' => 'string|max:150',
            'description' => 'string',
            'date' => 'date'
        ];
    }
}
