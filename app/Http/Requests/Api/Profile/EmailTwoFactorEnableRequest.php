<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class EmailTwoFactorEnableRequest extends FormRequest
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
            'challenge_id' => ['required', 'uuid'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
