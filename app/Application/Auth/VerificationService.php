<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Models\User;
use App\Models\VerificationChallenge;
use App\Notifications\Auth\VerificationCodeNotification;
use App\Support\Api\ApiProblemException;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Notifications\Dispatcher as NotificationDispatcher;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;

final class VerificationService
{
    private const VERIFICATION_CODE = '123456';

    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * @return array<string, mixed>
     */
    public function send(string $purpose, string $channel, string $target): array
    {
        return $this->createChallenge(
            purpose: $purpose,
            channel: $channel,
            target: $target,
            user: null,
            includeMaskedTarget: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function sendEmailTwoFactorEnable(User $user): array
    {
        return $this->createChallenge(
            purpose: 'email_two_factor_enable',
            channel: 'email',
            target: (string) $user->email,
            user: $user,
            includeMaskedTarget: true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function sendTwoFactorLogin(User $user): array
    {
        return $this->createChallenge(
            purpose: 'two_factor_login',
            channel: 'email',
            target: (string) $user->email,
            user: $user,
            includeMaskedTarget: true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function createChallenge(
        string $purpose,
        string $channel,
        string $target,
        ?User $user,
        bool $includeMaskedTarget,
    ): array
    {
        $code = self::VERIFICATION_CODE;

        $challenge = VerificationChallenge::query()->create([
            'purpose' => $purpose,
            'channel' => $channel,
            'target' => $target,
            'user_id' => $user?->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        if ($channel === 'email') {
            $this->sendEmailCode($target, $code, $purpose);
        }

        $payload = [
            'id' => $challenge->id,
            'expiresAt' => $this->dateString($challenge->expires_at),
        ];

        if ($includeMaskedTarget) {
            $payload['maskedTarget'] = $this->maskEmail($target);
        }

        if (! app()->isProduction()) {
            $payload['devCode'] = $code;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(string $id, string $code, ?string $purpose = null, bool $consume = false): array
    {
        $challenge = VerificationChallenge::query()->find($id);

        if (! $challenge instanceof VerificationChallenge) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($purpose !== null && $challenge->purpose !== $purpose) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        return $this->verifyChallenge($challenge, $code, $consume);
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyForUser(string $id, string $code, string $purpose, User $user, bool $consume = true): array
    {
        $challenge = VerificationChallenge::query()->find($id);

        if (! $challenge instanceof VerificationChallenge) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($challenge->purpose !== $purpose) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ((string) $challenge->user_id !== (string) $user->getKey()) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($challenge->target !== $user->email) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        return $this->verifyChallenge($challenge, $code, $consume);
    }

    /**
     * @return array<string, mixed>
     */
    private function verifyChallenge(VerificationChallenge $challenge, string $code, bool $consume): array
    {
        if ($challenge->consumed_at !== null) {
            throw new ApiProblemException('CHALLENGE_ALREADY_CONSUMED', 409);
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

        $fields = ['verified_at' => now()];

        if ($consume) {
            $fields['consumed_at'] = now();
        }

        $challenge->forceFill($fields)->save();

        return [
            'id' => $challenge->id,
            'verified' => true,
            'target' => $challenge->target,
            'userId' => $challenge->user_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function completePasswordReset(string $id, string $code, string $email, string $password): array
    {
        $challenge = VerificationChallenge::query()->find($id);

        if (! $challenge instanceof VerificationChallenge) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($challenge->purpose !== 'password_reset') {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        if ($challenge->target !== $email) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $this->verifyChallenge($challenge, $code, true);

        $user->forceFill(['password' => $password])->save();

        return [
            'changed' => true,
        ];
    }

    private function sendEmailCode(string $target, string $code, string $purpose): void
    {
        $notifiable = new AnonymousNotifiable;
        $notifiable->route('mail', $target);

        $this->notifications->send(
            $notifiable,
            new VerificationCodeNotification($code, $purpose),
        );
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

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($local === '') {
            return '***@'.$domain;
        }

        return substr($local, 0, 1).'***@'.$domain;
    }
}
