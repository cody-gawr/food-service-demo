<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\UserContract;

class CreateRestaurantProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Contracts\UserContract */
        $userContract = app(UserContract::class);
        return $userContract->hasPermissionInRestaurant($this->user(), 'unlock restaurant profile with only text', $this->route('restaurant'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'images' => 'array',
            'images.*' => 'required|mimes:png,jpg,jpeg,bmp',
            'videos' => 'array',
            'videos.*' => 'required|mimes:flv,avi,mp4,m3u8,ts,3gp,mov,wmv',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'description' => [
                'description' => 'The profile description of the restaurant.',
                'example' => 'Exercitationem qui velit architecto molestiae ea. Eos unde nam saepe. Fuga et est at dolor nisi et animi.',
            ],
            'images' => [
                'description' => 'The profile images.'
            ],
            'videos' => [
                'description' => 'The profile videos.'
            ]
        ];
    }
}
