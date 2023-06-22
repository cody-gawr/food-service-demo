<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
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
            'verification_code' => 'required|digits:6',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'verification_code' => [
                'description' => 'The code that the user is given.',
                'example' => '369431',
            ]
        ];
    }
}
