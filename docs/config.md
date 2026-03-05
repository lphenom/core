# Config

`LPhenom\Core\Config\Config` — immutable конфигурационный контейнер с поддержкой dot-notation ключей.

## Создание

```php
use LPhenom\Core\Config\Config;

$config = new Config([
    'app' => [
        'name'    => 'MyApp',
        'debug'   => false,
        'port'    => 8080,
        'allowed' => ['127.0.0.1', '::1'],
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'mydb',
    ],
]);
```

## Получение значений

### `get(string $key, mixed $default = null): mixed`

Возвращает значение по ключу. Поддерживает dot-notation. Если ключ не найден — возвращает `$default`.

```php
$name = $config->get('app.name');         // 'MyApp'
$host = $config->get('db.host');          // 'localhost'
$foo  = $config->get('missing', 'bar');   // 'bar'
```

### `getString(string $key, ?string $default = null): string`

Возвращает строковое значение. Бросает `ConfigException`, если значение не является строкой.

```php
$name = $config->getString('app.name'); // 'MyApp'

// С дефолтом:
$val = $config->getString('missing_key', 'default'); // 'default'

// Исключение, если значение не строка:
$config->getString('app.port'); // ConfigException: must be a string, got integer
```

### `getInt(string $key, ?int $default = null): int`

```php
$port = $config->getInt('app.port'); // 8080
```

### `getBool(string $key, ?bool $default = null): bool`

```php
$debug = $config->getBool('app.debug'); // false
```

### `getArray(string $key, ?array $default = null): array`

```php
$allowed = $config->getArray('app.allowed'); // ['127.0.0.1', '::1']
```

### `has(string $key): bool`

Проверяет наличие ключа (dot-notation поддерживается):

```php
$config->has('app.name');    // true
$config->has('app.missing'); // false
$config->has('db');          // true
```

## Dot-notation

Ключи с точкой интерпретируются как путь в многоуровневом массиве:

```php
$config = new Config([
    'level1' => [
        'level2' => [
            'level3' => 'deep value',
        ],
    ],
]);

$config->getString('level1.level2.level3'); // 'deep value'
```

## Совместная работа с EnvLoader

```php
use LPhenom\Core\EnvLoader\EnvLoader;
use LPhenom\Core\Config\Config;

$env = new EnvLoader();
$env->load(__DIR__ . '/.env');

$config = new Config([
    'db' => [
        'host'     => $env->get('DB_HOST', 'localhost'),
        'port'     => (int) ($env->get('DB_PORT', '3306')),
        'name'     => $env->get('DB_NAME', 'mydb'),
        'user'     => $env->get('DB_USER', 'root'),
        'password' => $env->get('DB_PASS', ''),
    ],
]);

$host = $config->getString('db.host');
```

## Исключения

| Класс | Когда бросается |
|-------|----------------|
| `ConfigException` | Значение не соответствует ожидаемому типу |

`ConfigException` наследует `LPhenomException → RuntimeException`.

## EnvLoader

```php
use LPhenom\Core\EnvLoader\EnvLoader;

$loader = new EnvLoader();
$loader->load(__DIR__ . '/.env');

$appEnv = $loader->get('APP_ENV', 'production');
```

### Формат `.env`

```dotenv
# Комментарии начинаются с #
APP_NAME=MyApp
APP_ENV=local
APP_DEBUG=true

# Значения можно заключать в кавычки
DB_PASS="my secret password"
DB_HOST='localhost'

# Значения могут содержать '='
DSN=mysql://user:pass@host/db?charset=utf8
```

