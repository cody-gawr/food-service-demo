<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NotLeader;

class FollowRequest extends FormRequest
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
            'uuid' => ['required', new NotLeader($this->user())]
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
