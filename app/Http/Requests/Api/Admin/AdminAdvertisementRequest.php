<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminAdvertisementRequest extends FormRequest
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
            'slug' => [
                $required,
                'string',
                'max:160',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('advertisements', 'slug')->ignore(is_string($id) ? $id : null),
            ],
            'placement' => ['sometimes', 'string', 'max:80'],
            'title' => [$required, 'string', 'max:160'],
            'label' => ['sometimes', 'nullable', 'string', 'max:160'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'image_url' => [$required, 'string', 'max:500'],
            'alt' => [$required, 'string', 'max:500'],
            'target_url' => [$required, 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
