<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminHomepageSlideRequest extends FormRequest
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
                Rule::unique('homepage_slides', 'slug')->ignore(is_string($id) ? $id : null),
            ],
            'label' => [$required, 'string', 'max:255'],
            'title' => [$required, 'string', 'max:255'],
            'lead' => [$required, 'string', 'max:2000'],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'image_url' => [$required, 'string', 'max:500'],
            'primary_cta_label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'primary_cta_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'secondary_cta_label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'secondary_cta_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
