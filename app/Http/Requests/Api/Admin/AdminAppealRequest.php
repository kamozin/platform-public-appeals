<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminAppealRequest extends FormRequest
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
                Rule::unique('public_appeals', 'slug')->ignore(is_string($id) ? $id : null),
            ],
            'title' => [$required, 'string', 'max:255'],
            'excerpt' => [$required, 'string', 'max:2000'],
            'description' => [$required, 'string'],
            'status' => [$required, 'string', Rule::in(['draft', 'checking', 'active', 'resolved'])],
            'status_label' => [$required, 'string', 'max:120'],
            'city' => [$required, 'string', 'max:160'],
            'district' => ['sometimes', 'nullable', 'string', 'max:160'],
            'category' => [$required, 'string', 'max:160'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'support_count' => ['sometimes', 'integer', 'min:0'],
            'views_count' => ['sometimes', 'integer', 'min:0'],
            'comments_count' => ['sometimes', 'integer', 'min:0'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_public' => ['sometimes', 'boolean'],
            'attachments' => ['sometimes', 'array'],
            'attachments.*.type' => ['required_with:attachments', 'string', 'max:60'],
            'attachments.*.url' => ['required_with:attachments', 'string', 'max:500'],
            'attachments.*.title' => ['required_with:attachments', 'string', 'max:255'],
            'timeline' => ['sometimes', 'array'],
            'timeline.*.status' => ['required_with:timeline', 'string', 'max:60'],
            'timeline.*.title' => ['required_with:timeline', 'string', 'max:255'],
            'timeline.*.happened_at' => ['required_with:timeline', 'date'],
            'timeline.*.text' => ['required_with:timeline', 'string', 'max:4000'],
            'documents' => ['sometimes', 'array'],
            'documents.*.title' => ['required_with:documents', 'string', 'max:255'],
            'documents.*.url' => ['required_with:documents', 'string', 'max:500'],
            'official_response' => ['sometimes', 'nullable', 'array'],
            'official_response.title' => ['required_with:official_response', 'string', 'max:255'],
            'official_response.text' => ['required_with:official_response', 'string', 'max:4000'],
            'official_response.received_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
