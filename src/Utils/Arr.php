<?php

declare(strict_types=1);

namespace LPhenom\Core\Utils;

final class Arr
{
    /**
     * Get a value from a nested array using dot-notation key.
     *
     * @param array<string, mixed> $array
     * @param mixed                $default
     * @return mixed
     */
    public static function getDot(array $array, string $key, mixed $default = null): mixed
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
    public static function setDot(array &$array, string $key, mixed $value): void
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

