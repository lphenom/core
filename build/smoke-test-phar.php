#!/usr/bin/env php
<?php

/**
 * PHAR smoke-test: require the built PHAR and verify autoloading works.
 *
 * Usage: php build/smoke-test-phar.php /path/to/lphenom-core.phar
 */

declare(strict_types=1);

$pharFile = $argv[1] ?? dirname(__DIR__) . '/lphenom-core.phar';

if (!file_exists($pharFile)) {
    fwrite(STDERR, "PHAR not found: {$pharFile}" . PHP_EOL);
    exit(1);
}

require $pharFile;

$config = new \LPhenom\Core\Config\Config(['app' => ['name' => 'lphenom']]);
assert($config->getString('app.name') === 'lphenom', 'Config failed');
echo 'smoke-test: config ok' . PHP_EOL;

$clock = new \LPhenom\Core\Clock\SystemClock();
$now   = $clock->now();
assert($now instanceof DateTimeImmutable, 'Clock failed');
echo 'smoke-test: clock ok' . PHP_EOL;

$container = new \LPhenom\Core\Container\Container();
assert($container->has('x') === false, 'Container has() failed');
echo 'smoke-test: container ok' . PHP_EOL;

$loader = new \LPhenom\Core\EnvLoader\EnvLoader();
echo 'smoke-test: envloader ok' . PHP_EOL;

echo '=== PHAR smoke-test: OK ===' . PHP_EOL;

