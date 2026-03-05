<?php

declare(strict_types=1);

namespace LPhenom\Core\Clock;

interface ClockInterface
{
    /**
     * Return the current date and time.
     */
    public function now(): \DateTimeImmutable;
}

