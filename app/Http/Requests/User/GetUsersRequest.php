<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class GetUsersRequest extends FormRequest
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
            'keyword' => 'filled|array|min:1|max:3',
            'keyword.first_name' => 'filled|string',
            'keyword.last_name' => 'filled|string',
            'keyword.name' => 'filled|string',
            'keyword.address' => 'filled|string',
        ];
    }

    public function bodyParameters()
    {
        return [
            'keyword' => [
                'description' => 'The keyword which is used for filtering users.',
                'example' => 'weber',
            ],
        ];
    }
}
