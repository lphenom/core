<?php

declare(strict_types=1);

namespace LPhenom\Core\Clock;

/**
 * System clock — returns the current wall-clock time.
 */
final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
