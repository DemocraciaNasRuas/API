<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPost extends FormRequest
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
            'user_id' => 'required|numeric|exists:users,id',
            'address_id' => 'required|numeric|exists:addresses,id',
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'date' => 'required|date'
        ];
    }
}
