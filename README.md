# lphenom/core

[![PHP](https://img.shields.io/badge/php-%3E%3D8.0-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHPUnit](https://img.shields.io/badge/tests-PHPUnit-blue)](phpunit.xml)
[![PHPStan](https://img.shields.io/badge/static%20analysis-PHPStan%20lvl8-blueviolet)](phpstan.neon)

> Фундаментальный пакет фреймворка **LPhenom** — PHP-фреймворка нового поколения, совместимого с [KPHP](https://github.com/VKCOM/kphp).

---

## Что это такое

`lphenom/core` — это фундамент экосистемы LPhenom. Пакет предоставляет базовые строительные блоки, которые работают как в классическом PHP-режиме (shared hosting, Apache/Nginx), так и после компиляции через KPHP в высокопроизводительный бинарник.

### Принципы

- ✅ **KPHP-совместимость** — никакого `Reflection`, `eval`, `new $class()`, variable variables
- ✅ **Строгая типизация** — `declare(strict_types=1)` в каждом файле
- ✅ **Явные зависимости** — всё регистрируется вручную, без автовайринга
- ✅ **Минималистично** — 0 production-зависимостей
- ✅ **Хорошо покрыто тестами** — 52 теста, phpstan level 8

---

## Установка

```bash
composer require lphenom/core
```

Требования: **PHP >= 8.0**

---

## Компоненты

### Config — иммутабельная конфигурация

```php
use LPhenom\Core\Config\Config;

$config = new Config([
    'app' => [
        'name'  => 'MyApp',
        'debug' => false,
        'port'  => 8080,
    ],
    'db' => ['host' => 'localhost'],
]);

$config->getString('app.name');          // 'MyApp'
$config->getInt('app.port');             // 8080
$config->getBool('app.debug');           // false
$config->has('db.host');                 // true
$config->get('missing.key', 'default'); // 'default'
```

Поддерживает **dot-notation** для вложенных ключей. Подробнее: [docs/config.md](docs/config.md)

---

### EnvLoader — загрузка .env-файлов

```php
use LPhenom\Core\EnvLoader\EnvLoader;

$env = new EnvLoader();
$env->load(__DIR__ . '/.env');

$dbHost = $env->get('DB_HOST', 'localhost');
```

Поддерживает комментарии `#`, строки в кавычках, символ `=` в значениях. Без сторонних зависимостей.

---

### Container — DI-контейнер без Reflection

```php
use LPhenom\Core\Container\Container;

$container = new Container();

// Регистрация singleton (shared по умолчанию)
$container->set('db', function (Container $c): PDO {
    return new PDO('sqlite::memory:');
});

// Регистрация factory (новый объект при каждом get)
$container->set('request', function (Container $c): MyRequest {
    return new MyRequest();
}, shared: false);

$db = $container->get('db');
$container->has('cache'); // false
```

Защита от циклических зависимостей встроена. Подробнее: [docs/container.md](docs/container.md)

---

### Clock

```php
use LPhenom\Core\Clock\ClockInterface;
use LPhenom\Core\Clock\SystemClock;

$clock = new SystemClock();
$now = $clock->now(); // DateTimeImmutable
```

---

### Утилиты

```php
use LPhenom\Core\Utils\Str;
use LPhenom\Core\Utils\Arr;

Str::startsWith('hello world', 'hello'); // true
Str::endsWith('hello world', 'world');   // true

$data = ['a' => ['b' => ['c' => 42]]];
Arr::getDot($data, 'a.b.c');             // 42

$arr = [];
Arr::setDot($arr, 'x.y', 'value');
// $arr = ['x' => ['y' => 'value']]
```

---

## Разработка

### Запуск через Docker

```bash
make up       # Поднять окружение
make test     # Запустить тесты
make lint     # Проверить стиль кода
make phpstan  # Статический анализ
make down     # Остановить контейнеры
```

### Локально

```bash
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run --diff
```

---

## Структура пакета

```
src/
├── Clock/          # ClockInterface, SystemClock
├── Config/         # Config, ConfigException
├── Container/      # Container, ContainerException
├── EnvLoader/      # EnvLoader
├── Exception/      # LPhenomException (base)
└── Utils/          # Arr, Str
tests/
docs/
├── config.md
└── container.md
```

---

## Лицензия

MIT © LPhenom Contributors. См. [LICENSE](LICENSE).
