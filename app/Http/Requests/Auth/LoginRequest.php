<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'platform' => 'required|in:mobile,web',
            'email' => 'required_if:name,null|email|max:255',
            'name' => 'required_if:email,null|string|max:255',
            'password' => 'required|string|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'platform' => [
                'description' => 'The platform name the user is using. It should be one of web or mobile.',
                'example' => 'mobile'
            ],
            'email' => [
                'description' => 'The email of the user',
                'example' => 'jakayla79@example.net'
            ],
            'name' => [
                'description' => 'The unique id or nick name of the user',
                'example' => 'jacey.mcglynn'
            ],
            'password' => [
                'description' => 'The password of the user',
                'example' => ''
            ]
        ];
    }

}
