<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use App\Rules\ValidVerificationCode;

#[BodyParam(name: 'password_confirmation', type: 'string', description: 'The field must be the same as password.', example: '')]
class ResetPasswordRequest extends FormRequest
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
            'code' => ['required', 'digits:6', new ValidVerificationCode()],
            'password' => 'required|string|confirmed|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@!$#%]).*$/|max:1024',
        ];
    }

    public function messages()
    {
        return [
            'password.min' => 'Password must be more than 6 characters long.',
            'password.regex' => 'Password must contain at least 3 characters consisted of uppercase/lowercase character and non-alphanumeric, unicode character(e.g @!$#%).'
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'code' => [
                'description' => 'The verfication code that is used for resetting a password.',
                'example' => '712675'
            ],
        ];
    }
}
