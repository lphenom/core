<?php

declare(strict_types=1);

namespace LPhenom\Core\Utils;

final class Arr
{
    /**
     * Check if a key exists in a nested array using dot-notation.
     *
     * @param array<string, mixed> $array
     */
    public static function hasDot(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        $parts   = explode('.', $key);
        $current = $array;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return false;
            }
            $current = $current[$part];
        }

        return true;
    }

    /**
     * Get a value from a nested array using dot-notation key.
     *
     * @param array<string, mixed> $array
     * @param mixed                $default
     * @return mixed
     */
    public static function getDot(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        $parts = explode('.', $key);
        $current = $array;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Set a value in a nested array using dot-notation key.
     *
     * @param array<string, mixed> $array
     * @param mixed                $value
     */
    public static function setDot(array &$array, string $key, $value): void
    {
        $parts = explode('.', $key);
        $current = &$array;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }
}
