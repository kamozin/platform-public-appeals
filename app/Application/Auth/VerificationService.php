<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Models\User;
use App\Models\VerificationChallenge;
use App\Support\Api\ApiProblemException;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Hash;

final class VerificationService
{
    /**
     * @return array<string, mixed>
     */
    public function send(string $purpose, string $channel, string $target): array
    {
        $code = app()->isProduction() ? (string) random_int(100000, 999999) : '123456';

        $challenge = VerificationChallenge::query()->create([
            'purpose' => $purpose,
            'channel' => $channel,
            'target' => $target,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $payload = [
            'id' => $challenge->id,
            'expiresAt' => $this->dateString($challenge->expires_at),
        ];

        if (! app()->isProduction()) {
            $payload['devCode'] = $code;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(string $id, string $code, ?string $purpose = null): array
    {
        $challenge = VerificationChallenge::query()->find($id);

        if (! $challenge instanceof VerificationChallenge) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($purpose !== null && $challenge->purpose !== $purpose) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if (CarbonImmutable::parse($challenge->expires_at)->isPast()) {
            throw new ApiProblemException('CHALLENGE_EXPIRED', 409);
        }

        if ($challenge->attempts >= 5) {
            throw new ApiProblemException('TOO_MANY_ATTEMPTS', 429);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->forceFill(['attempts' => $challenge->attempts + 1])->save();

            throw new ApiProblemException('INVALID_VERIFICATION_CODE', 422);
        }

        $challenge->forceFill(['verified_at' => now()])->save();

        return [
            'id' => $challenge->id,
            'verified' => true,
            'target' => $challenge->target,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function completePasswordReset(string $id, string $code, string $email, string $password): array
    {
        $result = $this->verify($id, $code, 'password_reset');

        if ($result['target'] !== $email) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $user->forceFill(['password' => $password])->save();

        return [
            'changed' => true,
        ];
    }

    private function dateString(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
