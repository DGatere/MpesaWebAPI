<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LipaNaMpesaPaybillRequests extends FormRequest
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
            'amount' => 'integer|required',
            'number' => 'required|min:12|max:12|regex:/^2547\d{8}$/',
            'account_reference' => 'required'
        ];
    }

    /**
     *  Validation messages to apply depending on the request rules
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => 'The amount is required',
            'amount.integer' => 'The amount must be an integer',
            'number.required'  => 'A number is required',
            'number.min' => 'The number must not be less than 12 characters',
            'number.max' => 'The number must not exceed 12 characters',
            'number.regex' => 'Numbers must be in the format 254xxxxxxxxx',
            'account_reference.required' => 'The account reference field is required'
        ];
    }
}
