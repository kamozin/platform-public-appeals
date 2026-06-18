<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Appeals;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAppealCommentRequest extends FormRequest
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
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
