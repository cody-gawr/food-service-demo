<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class GetRestaurantsRequest extends FormRequest
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
            'keyword.name' => 'filled|string',
            'keyword.address' => 'filled|string',
            'keyword.types' => 'filled|string',
            'keyword.rating' => 'filled|numeric',
            'order_by' => 'filled|array|min:1|max:3',
            'order_by.name' => 'filled|in:asc,desc',
            'order_by.address' => 'filled|in:asc,desc',
            'order_by.rating' => 'filled|in:asc,desc',

        ];
    }

    public function bodyParameters(): array
    {
        return [
            'query' => [
                'description' => 'The query object.'
            ],
            'query.name' => [
                'description' => 'The restaurant name that is used for filtering restaurants.',
                'example' => "McDonald's® (1004 W SHERIDAN)"
            ],
            'query.address' => [
                'description' => 'The restaurant address that is used for filtering restaurants.',
                'example' => '1004 W SHERIDAN'
            ],
            'query.types' => [
                'description' => 'The restaurant type that is used for filtering restaurants.',
                'example' => 'chicken or Chicken'
            ],
            'query.rating' => [
                'description' => 'The rating value that is used for filtering restaurants.',
                'example' => '4.7'
            ],
            'order_by' => [
                'description' => 'The object that is used for sorting the restaurants by restaurant_name, restaurant_address, rating.'
            ],
            'order_by.name' => [
                'description' => 'The keyword that is used for sorting the restaurants in a ascending order or a descending order. The field must be one of asc and desc.',
                'example' => 'asc'
            ],
            'order_by.address' => [
                'description' => 'The keyword that is used for sorting the restaurants in a ascending order or a descending order. The field must be one of asc and desc.',
                'example' => 'desc'
            ],
            'order_by.rating' => [
                'description' => 'The keyword that is used for sorting the restaurants in a ascending order or a descending order. The field must be one of asc and desc.',
                'example' => 'desc'
            ],
        ];
    }
}
