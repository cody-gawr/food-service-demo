<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApproveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_uuid' => 'required|exists:App\Models\User,uuid',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'uuid' => [
                'description' => 'The uuid of the user.',
                'example' => '44740319-a0ec-476d-97fb-b839b99d0033',
            ],
        ];
    }
}
