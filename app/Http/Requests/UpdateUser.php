<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
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
            'name' => 'string|filled',
            'email' => 'string|unique:users|filled',
            'gender' => 'in:M,F|filled',
            'dob' => 'string|filled',
            'password' => 'string|filled|min:8',
        ];
    }
}
