<?php

declare(strict_types=1);

namespace Marwa\MCP;

final class SlidingWindowRateLimiter implements RateLimiterInterface
{
    /** @var array<string, list<int>> */
    private array $windows = [];

    public function __construct(
        private readonly int $maxRequests = 60,
        private readonly int $windowSeconds = 60
    ) {
    }

    public function allows(string $key): bool
    {
        $now = time();
        $cutoff = $now - $this->windowSeconds;

        if (!isset($this->windows[$key])) {
            $this->windows[$key] = [];
        }

        $this->windows[$key] = array_filter(
            $this->windows[$key],
            static fn(int $timestamp): bool => $timestamp > $cutoff
        );

        if (count($this->windows[$key]) >= $this->maxRequests) {
            return false;
        }

        $this->windows[$key][] = $now;

        return true;
    }
}
