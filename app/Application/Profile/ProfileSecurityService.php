<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Application\Auth\VerificationService;
use App\Models\ApiToken;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class ProfileSecurityService
{
    public function __construct(private VerificationService $verification) {}

    /**
     * @return array<string, bool>
     */
    public function changePassword(User $user, ?ApiToken $currentToken, string $currentPassword, string $password): array
    {
        $this->assertCurrentPassword($user, $currentPassword);

        DB::transaction(function () use ($user, $currentToken, $password): void {
            $user->forceFill(['password' => $password])->save();

            $query = $user->apiTokens();

            if ($currentToken instanceof ApiToken) {
                $query->where('id', '!=', (string) $currentToken->getKey());
            }

            $query->delete();
        });

        return ['changed' => true];
    }

    /**
     * @return array<string, mixed>
     */
    public function sendEmailTwoFactorEnableCode(User $user, string $currentPassword): array
    {
        $this->assertCurrentPassword($user, $currentPassword);

        if ((bool) $user->email_two_factor_enabled) {
            throw new ApiProblemException('EMAIL_TWO_FACTOR_ALREADY_ENABLED', 409);
        }

        return $this->verification->sendEmailTwoFactorEnable($user);
    }

    /**
     * @return array<string, bool>
     */
    public function enableEmailTwoFactor(User $user, string $challengeId, string $code): array
    {
        if ((bool) $user->email_two_factor_enabled) {
            throw new ApiProblemException('EMAIL_TWO_FACTOR_ALREADY_ENABLED', 409);
        }

        $this->verification->verifyForUser(
            id: $challengeId,
            code: $code,
            purpose: 'email_two_factor_enable',
            user: $user,
            consume: true,
        );

        $user->forceFill([
            'email_two_factor_enabled' => true,
            'email_two_factor_confirmed_at' => now(),
        ])->save();

        return ['emailTwoFactorEnabled' => true];
    }

    /**
     * @return array<string, bool>
     */
    public function disableEmailTwoFactor(User $user, string $currentPassword): array
    {
        $this->assertCurrentPassword($user, $currentPassword);

        if (! (bool) $user->email_two_factor_enabled) {
            throw new ApiProblemException('EMAIL_TWO_FACTOR_NOT_ENABLED', 409);
        }

        $user->forceFill([
            'email_two_factor_enabled' => false,
            'email_two_factor_confirmed_at' => null,
        ])->save();

        return ['emailTwoFactorEnabled' => false];
    }

    private function assertCurrentPassword(User $user, string $currentPassword): void
    {
        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw new ApiProblemException('CURRENT_PASSWORD_INVALID', 422);
        }
    }
}
