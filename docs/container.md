# Container

`LPhenom\Core\Container\Container` — DI-контейнер без reflection, совместимый с KPHP.

Все зависимости регистрируются **явно** через фабрики (`callable`). Никакого `new $className()`, никакого Reflection API.

## Регистрация сервисов

### `set(string $id, callable $factory, bool $shared = true): void`

```php
use LPhenom\Core\Container\Container;

$container = new Container();

// Shared (singleton) — по умолчанию
$container->set('db', function (Container $c): PDO {
    return new PDO('sqlite::memory:');
});

// Not shared — новый экземпляр при каждом get()
$container->set('request', function (Container $c): object {
    return new MyRequest();
}, shared: false);
```

## Получение сервисов

### `get(string $id): object`

```php
$db = $container->get('db');
```

Бросает `ContainerException`, если:
- Сервис не зарегистрирован.
- Обнаружена циклическая зависимость.

### `has(string $id): bool`

```php
if ($container->has('cache')) {
    $cache = $container->get('cache');
}
```

## Shared vs Not-Shared

```php
$container->set('singleton', fn($c) => new \stdClass(), true);

$a = $container->get('singleton');
$b = $container->get('singleton');
var_dump($a === $b); // true — один и тот же объект

$container->set('factory', fn($c) => new \stdClass(), false);

$x = $container->get('factory');
$y = $container->get('factory');
var_dump($x === $y); // false — разные объекты
```

## Вложенные зависимости

Фабрика получает контейнер как аргумент и может разрешать другие сервисы:

```php
$container->set('config', function (Container $c): Config {
    return new Config(['db' => ['host' => 'localhost']]);
});

$container->set('db', function (Container $c): PDO {
    /** @var Config $config */
    $config = $c->get('config');
    $host = $config->getString('db.host');
    return new PDO("mysql:host={$host}");
});

$db = $container->get('db');
```

## Защита от циклических зависимостей

Если сервис `A` запрашивает `B`, а `B` в свою очередь запрашивает `A` — контейнер бросит `ContainerException`:

```php
$container->set('a', fn(Container $c) => $c->get('b'));
$container->set('b', fn(Container $c) => $c->get('a'));

$container->get('a'); // ContainerException: Circular dependency detected while resolving "a"
```

## Исключения

| Класс | Когда бросается |
|-------|----------------|
| `ContainerException` | Сервис не зарегистрирован |
| `ContainerException` | Обнаружена циклическая зависимость |

`ContainerException` наследует `LPhenomException → RuntimeException`.

## KPHP-совместимость

- ❌ Нет `Reflection`
- ❌ Нет `new $id()`
- ✅ Все зависимости регистрируются явно
- ✅ Строгая типизация `declare(strict_types=1)`
- ✅ Callable-фабрики совместимы с KPHP

## Clock

```php
use LPhenom\Core\Clock\SystemClock;
use LPhenom\Core\Clock\ClockInterface;

$container->set(ClockInterface::class, fn($c) => new SystemClock());

/** @var ClockInterface $clock */
$clock = $container->get(ClockInterface::class);
$now = $clock->now(); // DateTimeImmutable
```

