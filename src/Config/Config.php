<?php

declare(strict_types=1);

namespace LPhenom\Core\Config;

use LPhenom\Core\Utils\Arr;

/**
 * Immutable configuration container with dot-notation key support.
 */
final class Config
{
    /** @var array<string, mixed> */
    private array $data;

    /** @param array<string, mixed> $data */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get a raw value by dot-notation key.
     *
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::getDot($this->data, $key, $default);
    }

    /**
     * Check whether a key exists (dot-notation supported).
     */
    public function has(string $key): bool
    {
        return Arr::hasDot($this->data, $key);
    }

    /**
     * Get a string value. Throws ConfigException if the value is not a string.
     *
     * @throws ConfigException
     */
    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->get($key, $default);

        if (!is_string($value)) {
            throw new ConfigException(
                sprintf('Config key "%s" must be a string, got %s.', $key, gettype($value))
            );
        }

        return $value;
    }

    /**
     * Get an integer value. Throws ConfigException if the value is not an integer.
     *
     * @throws ConfigException
     */
    public function getInt(string $key, ?int $default = null): int
    {
        $value = $this->get($key, $default);

        if (!is_int($value)) {
            throw new ConfigException(
                sprintf('Config key "%s" must be an integer, got %s.', $key, gettype($value))
            );
        }

        return $value;
    }

    /**
     * Get a boolean value. Throws ConfigException if the value is not a boolean.
     *
     * @throws ConfigException
     */
    public function getBool(string $key, ?bool $default = null): bool
    {
        $value = $this->get($key, $default);

        if (!is_bool($value)) {
            throw new ConfigException(
                sprintf('Config key "%s" must be a boolean, got %s.', $key, gettype($value))
            );
        }

        return $value;
    }

    /**
     * Get an array value. Throws ConfigException if the value is not an array.
     *
     * @return array<mixed>
     * @throws ConfigException
     */
    public function getArray(string $key, ?array $default = null): array
    {
        $value = $this->get($key, $default);

        if (!is_array($value)) {
            throw new ConfigException(
                sprintf('Config key "%s" must be an array, got %s.', $key, gettype($value))
            );
        }

        return $value;
    }
}
