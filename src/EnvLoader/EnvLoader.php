<?php

declare(strict_types=1);

namespace LPhenom\Core\EnvLoader;

/**
 * Simple .env file loader — no third-party libraries.
 * Supports: key=value, quoted values ("value" or 'value'),
 * comments (#), and empty lines.
 */
final class EnvLoader
{
    /**
     * Load variables from a .env file into $_ENV and putenv().
     *
     * @throws \RuntimeException if the file cannot be read.
     */
    public function load(string $filePath): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(sprintf('EnvLoader: file "%s" not found or not readable.', $filePath));
        }

        $lines = file($filePath); // KPHP: file() supports only 1 argument

        if ($lines === false) {
            throw new \RuntimeException(sprintf('EnvLoader: unable to read file "%s".', $filePath));
        }

        foreach ($lines as $rawLine) {
            $line = trim((string)$rawLine);

            // Skip empty lines and comments
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }

            // Split only on the first '='
            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $name  = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));

            if ($name === '') {
                continue;
            }

            $value = $this->stripQuotes($value);

            $_ENV[$name] = $value;
        }
    }

    /**
     * Get a loaded environment variable.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        if (isset($_ENV[$key]) && is_string($_ENV[$key])) {
            return $_ENV[$key];
        }


        return $default;
    }

    /**
     * Strip surrounding quotes from a value string.
     */
    private function stripQuotes(string $value): string
    {
        $len = strlen($value);

        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];

            if (($first === '"' && $last === '"') || ($first === '\'' && $last === '\'')) {
                return substr($value, 1, $len - 2);
            }
        }

        return $value;
    }
}
