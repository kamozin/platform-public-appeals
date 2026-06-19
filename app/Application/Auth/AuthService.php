<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Models\ApiToken;
use App\Models\User;
use App\Models\VerificationChallenge;
use App\Support\Api\ApiProblemException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AuthService
{
    public function __construct(private readonly VerificationService $verification) {}

    /**
     * @param  array{name: string, email: string, phone?: string|null, password: string, notifications?: bool|null}  $payload
     * @return array<string, mixed>
     */
    public function register(array $payload): array
    {
        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => $payload['password'],
            'notifications_enabled' => $payload['notifications'] ?? true,
        ]);

        return $this->issueToken($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function login(string $login, string $password): array
    {
        $user = User::query()
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (! $user instanceof User || ! Hash::check($password, (string) $user->password)) {
            throw new ApiProblemException('INVALID_CREDENTIALS', 401);
        }

        if ((bool) $user->email_two_factor_enabled) {
            $challenge = $this->verification->sendTwoFactorLogin($user);

            return [
                'requiresTwoFactor' => true,
                'challengeId' => $challenge['id'],
                'expiresAt' => $challenge['expiresAt'],
                'maskedTarget' => $challenge['maskedTarget'],
                ...$this->devCodePayload($challenge),
            ];
        }

        return $this->issueToken($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyTwoFactor(string $challengeId, string $code): array
    {
        $challenge = VerificationChallenge::query()->find($challengeId);

        if (! $challenge instanceof VerificationChallenge) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $user = $challenge->user;

        if (! $user instanceof User || ! (bool) $user->email_two_factor_enabled) {
            throw new ApiProblemException('CHALLENGE_NOT_FOUND', 404);
        }

        $this->verification->verifyForUser(
            id: $challengeId,
            code: $code,
            purpose: 'two_factor_login',
            user: $user,
            consume: true,
        );

        return $this->issueToken($user);
    }

    public function requireUser(Request $request): User
    {
        $token = $this->resolveToken($request);

        if (! $token instanceof ApiToken) {
            throw new ApiProblemException('UNAUTHORIZED', 401);
        }

        $user = $token->user;

        if (! $user instanceof User) {
            throw new ApiProblemException('UNAUTHORIZED', 401);
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return $user;
    }

    public function requireAdmin(Request $request): User
    {
        $user = $this->requireUser($request);

        if (! (bool) $user->is_admin) {
            throw new ApiProblemException('FORBIDDEN', 403);
        }

        return $user;
    }

    public function userOrNull(Request $request): ?User
    {
        if (! $this->hasBearerToken($request)) {
            return null;
        }

        return $this->requireUser($request);
    }

    public function logout(Request $request): void
    {
        $token = $this->resolveToken($request);

        if (! $token instanceof ApiToken) {
            throw new ApiProblemException('UNAUTHORIZED', 401);
        }

        $token->delete();
    }

    public function currentToken(Request $request): ?ApiToken
    {
        return $this->resolveToken($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function userPayload(User $user): array
    {
        $avatarPath = $this->avatarPathOrNull($user->avatar_path);

        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'notificationsEnabled' => (bool) $user->notifications_enabled,
            'isAdmin' => (bool) $user->is_admin,
            'avatarUrl' => $avatarPath !== null ? '/storage/'.$avatarPath : null,
            'emailTwoFactorEnabled' => (bool) $user->email_two_factor_enabled,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function issueToken(User $user): array
    {
        $plainToken = Str::random(80);

        $user->apiTokens()->create([
            'token_hash' => hash('sha256', $plainToken),
            'name' => 'api',
        ]);

        return [
            'token' => $plainToken,
            'tokenType' => 'Bearer',
            'user' => $this->userPayload($user),
        ];
    }

    /**
     * @param  array<string, mixed>  $challenge
     * @return array<string, string>
     */
    private function devCodePayload(array $challenge): array
    {
        if (! array_key_exists('devCode', $challenge)) {
            return [];
        }

        return ['devCode' => (string) $challenge['devCode']];
    }

    private function resolveToken(Request $request): ?ApiToken
    {
        $authorization = $request->headers->get('Authorization', '');

        if (! str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        $plainToken = trim(substr($authorization, 7));

        if ($plainToken === '') {
            return null;
        }

        return ApiToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();
    }

    private function hasBearerToken(Request $request): bool
    {
        $authorization = $request->headers->get('Authorization', '');

        return str_starts_with($authorization, 'Bearer ');
    }

    private function avatarPathOrNull(?string $path): ?string
    {
        if ($path === null || $path === '' || $path === '0') {
            return null;
        }

        return $path;
    }
}
