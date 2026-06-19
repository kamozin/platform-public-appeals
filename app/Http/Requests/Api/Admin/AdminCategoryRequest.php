<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';
        $id = $this->route('id');

        return [
            'group_slug' => [$required, 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'group_title' => ['sometimes', 'string', 'max:160'],
            'slug' => [
                $required,
                'string',
                'max:120',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('categories', 'slug')->ignore(is_string($id) ? $id : null),
            ],
            'title' => [$required, 'string', 'max:160'],
            'description' => [$required, 'string', 'max:1000'],
            'icon' => ['sometimes', 'string', 'max:60'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
