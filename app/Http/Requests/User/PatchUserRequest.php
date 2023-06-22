<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

#[QueryParam(name: '_method', type: 'string', description: 'HTTP Method name.',example: 'PATCH')]
class PatchUserRequest extends FormRequest
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
            'name' => 'sometimes|string|max:1024|unique:users,name',
            'email' => 'sometimes|string|max:1024|unique:users,email',
            'password' => 'sometimes|string|confirmed|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@!$#%]).*$/|max:1024',
            'avatar' => 'filled|image',
            'interesting' => 'filled|array',
            'intrigue' => 'filled|array',
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
            'password' => [
                'description' => '',
                'example' => '',
            ],
            'avatar' => [
                'description' => 'The Avatar of the user.',
            ],
            'interesting' => [
                'description' => 'The array value which indicates what bring the user to eat that.',
                'example' => "['I LOVE FOOD', 'HELP OTHERS']"
            ],
            'intrigue' => [
                'description' => 'The array value which indicates what intrigue the user.',
                'example' => "['NEW FOODS', 'DAILY SPECIALS']"
            ]
        ];
    }
}
