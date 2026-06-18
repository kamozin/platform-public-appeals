<?php

declare(strict_types=1);

namespace App\Support\Api;

use RuntimeException;

final class ApiProblemException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public readonly string $errorCode,
        public readonly int $statusCode,
        public readonly array $details = [],
    ) {
        parent::__construct($errorCode);
    }
}
