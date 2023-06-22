<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
        return [
            'email' => 'required|email|exists:App\Models\User,email'
        ];
    }

    public function bodyParameters(): array
    {
        return [
            // 'code' => [
            //     'description' => 'The verfication code that is used for resetting a password.',
            //     'example' => '712675'
            // ],
            'email' => [
                'description' => 'The email of the user which is given the 6 digit verifiation code to reset a password.',
                'example' => 'schneider.lauryn@example.net'
            ]
        ];
    }
}
