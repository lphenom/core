<?php

/**
 * KPHP build entrypoint for lphenom/core.
 *
 * This file is the entry point used by KPHP to compile the library into a binary.
 * It requires all source files explicitly — KPHP does not support autoloading.
 *
 * The binary produced here is not meant to do anything useful at runtime;
 * it just proves that the code compiles cleanly under KPHP.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/Exception/LPhenomException.php';
require_once __DIR__ . '/../src/Config/ConfigException.php';
require_once __DIR__ . '/../src/Container/ContainerException.php';
require_once __DIR__ . '/../src/Container/ServiceFactoryInterface.php';
require_once __DIR__ . '/../src/Utils/Arr.php';
require_once __DIR__ . '/../src/Utils/Str.php';
require_once __DIR__ . '/../src/Config/Config.php';
require_once __DIR__ . '/../src/Container/Container.php';
require_once __DIR__ . '/../src/Clock/ClockInterface.php';
require_once __DIR__ . '/../src/Clock/SystemClock.php';
require_once __DIR__ . '/../src/EnvLoader/EnvLoader.php';

// Smoke-test: instantiate core classes to confirm the binary works
$config = new \LPhenom\Core\Config\Config(['app' => ['name' => 'lphenom', 'debug' => false]]);
echo 'app.name = ' . $config->getString('app.name') . PHP_EOL;

$clock  = new \LPhenom\Core\Clock\SystemClock();
echo 'clock ok' . PHP_EOL;

$loader = new \LPhenom\Core\EnvLoader\EnvLoader();
echo 'envloader ok' . PHP_EOL;

$container = new \LPhenom\Core\Container\Container();
echo 'container ok' . PHP_EOL;

echo 'lphenom/core KPHP build: OK' . PHP_EOL;

