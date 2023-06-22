<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Illuminate\Validation\Rule;

#[BodyParam(name: 'password_confirmation', type: 'string', description: 'The field must be the same as password.', example: '')]
class RegisterRequest extends FormRequest
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
            'first_name' => 'sometimes|string|max:1024',
            'last_name' => 'sometimes|string|max:1024',
            'address' => 'sometimes|string|max:1024',
            'name' => 'required|string|max:1024|unique:users,name',
            'email' => ['required', 'string', 'email', 'max:1024', Rule::unique('users', 'email')],
            'password' => 'required|string|confirmed|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@!$#%]).*$/|max:1024',
            'avatar' => 'filled|image',
            'role' => ['filled', Rule::in(['beginner owner'])],
            // 'documents' => 'required_with:role|array',
            // 'documents.*' => 'mimes:png,jpg,jpeg,bmp,doc,docx,pdf',
            'restaurant_uuid' => 'required_with:role|exists:App\Models\Restaurant,uuid',
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
            'first_name' => [
                'description' => 'The first name of the user.',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'The last name of the user.',
                'example' => 'Doe',
            ],
            'address' => [
                'description' => 'The address of the user.',
                'example' => '7419 Gorczany Plaza West Tatyana, SD 14071'
            ],
            'name' => [
                'description' => 'The unique id(nick name) of the user.',
                'example' => 'rosario.cruickshank'
            ],
            'email' => [
                'description' => 'The email of the user.',
                'example' => 'pdouglas@example.org'
            ],
            'avatar' => [
                'description' => 'The Avatar of the user.',
            ],
            'role' => [
                'description' => 'When the user claims he is an owner of the restaurant, the role should be assigned to the user.',
                'example' => 'beginner owner',
            ],
            'restaurant_uuid' => [
                'description' => 'The uuid of the restaurant which the user claims it belongs to him.',
            ],
            // 'documents' => [
            //     'description' => 'The files which will be used for proving the user is the owner of the restaurant.',
            // ]
        ];
    }
}
