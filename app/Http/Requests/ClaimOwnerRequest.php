<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimOwnerRequest extends FormRequest
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
            'documents' => 'required|array',
            'documents.*' => 'mimes:png,jpg,jpeg,bmp,doc,docx,pdf',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'documents' => [
                'description' => 'The files which will be used for proving the user is the owner of the restaurant.',
            ]
        ];
    }
}
