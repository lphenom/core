<?php

declare(strict_types=1);

namespace LPhenom\Core\Utils;

final class Str
{
    /**
     * Check if a string starts with the given substring.
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Check if a string ends with the given substring.
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }
}
