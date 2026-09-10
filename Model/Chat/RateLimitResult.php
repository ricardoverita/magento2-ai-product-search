<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Chat;
final class RateLimitResult
{
    public function __construct(
        private readonly bool $allowed,
        private readonly int $retryAfter = 0
    ) {
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
