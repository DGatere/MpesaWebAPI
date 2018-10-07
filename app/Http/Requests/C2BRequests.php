<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class C2BRequests extends FormRequest
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
            'Amount' => 'integer|required',
            'PhoneNumber' => 'required|min:12|max:12|regex:/^2547\d{8}$/',
            'BillRefNumber' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'Amount.required' => 'The amount is required',
            'Amount.integer' => 'The amount must be an integer',
            'PhoneNumber.required' => 'A number is required',
            'PhoneNumber.min' => 'The number must not be less than 12 characters',
            'PhoneNumber.max' => 'The number must not exceed 12 characters',
            'PhoneNumber.regex' => 'Numbers must be in the format 254xxxxxxxxx',
            'BillRefNumber.required' => 'The account reference field is required'
        ];
    }
}
