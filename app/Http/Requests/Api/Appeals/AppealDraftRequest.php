<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Appeals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppealDraftRequest extends FormRequest
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
        return [
            'category' => ['sometimes', 'nullable', 'string', 'max:120'],
            'submission_type' => ['sometimes', 'nullable', 'string', Rule::in(['complaint', 'appeal', 'proposal'])],
            'title' => ['sometimes', 'nullable', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'urgency' => ['sometimes', 'nullable', 'string', Rule::in(['normal', 'urgent'])],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_visibility' => ['sometimes', 'nullable', 'string', Rule::in(['public', 'hidden'])],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }
}
