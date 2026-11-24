<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:80'],
            'technology' => ['nullable', 'string', 'max:60'],
            'featured' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'sort' => ['nullable', Rule::in(['sort_order', '-published_at', 'title'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
