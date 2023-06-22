<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('unlock-sponsored-posts-and-ads', [$this->route('restaurant')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'images' => 'array',
            'images.*' => 'required|file|mimes:png,jpg,jpeg,bmp',
            'videos' => 'array',
            'videos.*' => 'required|file|mimes:flv,avi,mp4,m3u8,ts,3gp,mov,wmv',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'The post title of the restaurant.',
                'example' => 'Sponsored Post Title.',
            ],
            'description' => [
                'description' => 'The post description of the restaurant.',
                'example' => 'Exercitationem qui velit architecto molestiae ea. Eos unde nam saepe. Fuga et est at dolor nisi et animi.',
            ],
            'images' => [
                'description' => 'The post images.'
            ],
            'videos' => [
                'description' => 'The post videos.'
            ]
        ];
    }
}
