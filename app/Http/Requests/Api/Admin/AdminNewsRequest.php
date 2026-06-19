<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminNewsRequest extends FormRequest
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
                Rule::unique('news_posts', 'slug')->ignore(is_string($id) ? $id : null),
            ],
            'title' => [$required, 'string', 'max:255'],
            'excerpt' => [$required, 'string', 'max:2000'],
            'content' => [$required, 'string'],
            'category' => [$required, 'string', 'max:160'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
