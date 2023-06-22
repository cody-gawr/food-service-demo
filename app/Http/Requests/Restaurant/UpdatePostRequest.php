<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('unlock-sponsored-posts-and-ads', [$this->route('restaurant')])
            && Gate::check('restaurant-has-post', [$this->route('restaurant'), $this->route('post')]);
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
            'images.*.uuid' => 'nullable|uuid|exists:App\Models\Image,uuid',
            'images.*.file' => 'required|file|mimes:png,jpg,jpeg,bmp',
            'videos' => 'array',
            'videos.*.uuid' => 'nullable|uuid|exists:App\Models\Video,uuid',
            'videos.*.file' => 'required|file|mimes:flv,avi,mp4,m3u8,ts,3gp,mov,wmv',
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
            'images.*.uuid' => [
                'description' => 'The uuid of the image to be updated.'
            ],
            'images.*.file' => [
                'description' => 'The image file to be updated.'
            ],
            'videos' => [
                'description' => 'The post videos.'
            ],
            'videos.*.uuid' => [
                'description' => 'The uuid of the video to be updated.'
            ],
            'videos.*.file' => [
                'description' => 'The video file to be updated.'
            ],
        ];
    }
}
