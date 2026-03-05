<?php

declare(strict_types=1);

namespace LPhenom\Core\Utils;

final class Str
{
    /**
     * Check if a string starts with the given substring.
     *
     * KPHP-compatible: uses substr() instead of str_starts_with().
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Check if a string ends with the given substring.
     *
     * KPHP-compatible: uses substr() instead of str_ends_with().
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $len = strlen($needle);

        return substr($haystack, -$len) === $needle;
    }

    /**
     * Check if a string contains the given substring.
     *
     * KPHP-compatible: uses strpos() instead of str_contains().
     */
    public static function contains(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return strpos($haystack, $needle) !== false;
    }
}
