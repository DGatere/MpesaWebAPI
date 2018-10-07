<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class B2BRequests extends FormRequest
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
            'command_id' => 'required',
            'amount' => 'integer|required',
            'shortcode' => 'required|min:6|max:6|regex:/^\d{6}$/',
            'account_reference' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'command_id.required' => 'Command ID is required',
            'amount.required' => 'The amount is required',
            'amount.integer' => 'The amount must be an integer',
            'shortcode.required'  => 'A shortcode is required',
            'shortcode.regex' => 'The shortcode format is invalid',
            'shortcode.min' => 'The shortcode must not be less than 6 characters',
            'shortcode.max' => 'The shortcode must not exceed 6 characters',
            'account_reference.required' => 'The account reference field is required'
        ];
    }
}
