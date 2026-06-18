<?php

declare(strict_types=1);

namespace App\Application\Appeals;

use App\Models\User;

final readonly class AppealDraftAccess
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
    ) {}

    public function hasAnyCredential(): bool
    {
        return $this->user instanceof User || $this->guestTokenHash() !== null;
    }

    public function guestTokenHash(): ?string
    {
        if ($this->guestToken === null || $this->guestToken === '') {
            return null;
        }

        return hash('sha256', $this->guestToken);
    }
}
