# KPHP Compatibility Guide

Этот документ описывает **все ограничения и правила**, которые были выявлены при разработке и сборке `lphenom/core` под KPHP.  
Соблюдение этих правил обязательно для всех пакетов экосистемы LPhenom.

---

## Как работает KPHP-сборка

KPHP (`vkcom/kphp`) компилирует PHP-код в **статический C++ бинарник**. При этом:

- KPHP **не использует PHP runtime** при компиляции — он сам парсит PHP-код;
- скомпилированный бинарник **не зависит от PHP** вообще;
- KPHP имеет собственный строгий type inference и откажет в компиляции, если типы неоднозначны;
- KPHP базируется на Docker-образе `vkcom/kphp` (Ubuntu 20.04 focal + PHP 7.4-vkext для tooling).

> **Важно:** минимальная версия PHP для **runtime/разработки** — 8.1. KPHP-образ содержит PHP 7.4 только как инструмент компилятора, это детали реализации — не ваш runtime.

---

## Категорически запрещено (не компилируется под KPHP)

### 1. Reflection API

```php
// ❌ ЗАПРЕЩЕНО
$ref = new ReflectionClass(MyService::class);
$ref->getMethods();
```

**Альтернатива:** явная регистрация зависимостей через `ServiceFactoryInterface`.

---

### 2. Динамическая загрузка классов

```php
// ❌ ЗАПРЕЩЕНО
$class = 'My\\Service';
$obj = new $class();          // variable class instantiation

class_exists($class);         // динамическая проверка
call_user_func([$class, 'method']); // динамический вызов
```

**Альтернатива:** явный `new MyService()` или фабрика.

---

### 3. `eval()`

```php
// ❌ ЗАПРЕЩЕНО
eval('$x = 1;');
```

---

### 4. Переменные переменные (variable variables)

```php
// ❌ ЗАПРЕЩЕНО
$varName = 'foo';
$$varName = 'bar';
```

---

### 5. `callable` в обобщённых PHPDoc-типах

```php
// ❌ ЗАПРЕЩЕНО в массивах/дженериках
/** @var array<string, callable> $factories */
private array $factories;
```

**Альтернатива:** используйте конкретный интерфейс:

```php
// ✅ ПРАВИЛЬНО
/** @var array<string, ServiceFactoryInterface> $factories */
private array $factories;
```

> Именно поэтому `Container` принимает `ServiceFactoryInterface`, а не `callable`.

---

### 6. Анонимные функции как тип в сигнатурах методов

```php
// ❌ ЗАПРЕЩЕНО — callable в аргументе с array-хранением
public function set(string $id, callable $factory): void { ... }
```

**Альтернатива:** интерфейс `ServiceFactoryInterface`.

---

### 7. `try/finally` без `catch`

```php
// ❌ ЗАПРЕЩЕНО
try {
    doSomething();
} finally {
    cleanup();
}
```

**Альтернатива:** всегда добавляйте хотя бы один `catch`:

```php
// ✅ ПРАВИЛЬНО
try {
    doSomething();
} catch (\Throwable $e) {
    $exception = $e;
} finally {
    cleanup();
}
```

> Именно так реализован `Container::get()` — сохраняет исключение в переменную, а потом бросает после `unset($this->resolving[$id])`.

---

### 8. Паттерн `!isset() + throw` напрямую

```php
// ❌ МОЖЕТ вызвать проблемы с парсером KPHP
if (!isset($this->factories[$id])) {
    throw new Exception('...');
}
$factory = $this->factories[$id]; // KPHP не знает, что null исключён
```

**Альтернатива:** явное присвоение с null-проверкой:

```php
// ✅ ПРАВИЛЬНО
$factory = $this->factories[$id] ?? null;
if ($factory === null) {
    throw new ContainerException('...');
}
// здесь $factory гарантированно не null
```

---

### 9. PHP 8.x функции-обёртки строк

```php
// ❌ ЗАПРЕЩЕНО (KPHP не поддерживает)
str_starts_with($str, 'prefix');
str_ends_with($str, 'suffix');
str_contains($str, 'needle');
```

**Альтернатива:** используйте утилиты `Str::`:

```php
// ✅ ПРАВИЛЬНО — через lphenom/core utils
use LPhenom\Core\Utils\Str;

Str::startsWith($str, 'prefix');
Str::endsWith($str, 'suffix');
Str::contains($str, 'needle');
```

Внутри реализованы через `substr()` / `strpos()`, которые KPHP поддерживает.

---

### 10. `file()` с флагами

```php
// ❌ ЗАПРЕЩЕНО — KPHP поддерживает file() только с 1 аргументом
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
```

**Альтернатива:** передавайте только путь, обрабатывайте строки вручную:

```php
// ✅ ПРАВИЛЬНО
$lines = file($filePath); // только 1 аргумент
foreach ($lines as $rawLine) {
    $line = trim((string)$rawLine);
    if ($line === '') continue;
    // ...
}
```

---

### 11. Intersection types (PHP 8.1+)

```php
// ❌ ЗАПРЕЩЕНО
function process(Countable&Iterator $col): void {}
```

**Альтернатива:** отдельный интерфейс.

---

### 12. First-class callable syntax (PHP 8.1+)

```php
// ❌ ЗАПРЕЩЕНО
$fn = strlen(...);
$fn = $obj->method(...);
```

**Альтернатива:** анонимная функция или метод интерфейса.

---

### 13. Fibers (PHP 8.1+)

KPHP имеет собственную модель конкурентности (coroutines через `fork()`). PHP Fibers не поддерживаются.

---

## Ограниченная поддержка (осторожно)

### `mixed` как возвращаемый тип

KPHP поддерживает `mixed`, но при использовании результата **нужен явный каст**:

```php
// В PHP — работает напрямую
$service = $container->get('my_service');
$service->doSomething(); // ❌ KPHP не знает тип

// ✅ В KPHP — нужен instance_cast()
$service = instance_cast($container->get('my_service'), MyService::class);
$service->doSomething();
```

> `Container::get()` возвращает `mixed` намеренно. При написании KPHP-кода всегда делайте `instance_cast()`.

---

### Именованные аргументы (PHP 8.0+)

```php
// ⚠️ Ограниченная поддержка в KPHP
$container->set('svc', $factory, shared: false);
```

Используйте с осторожностью, тестируйте через `Dockerfile.check`.

---

### `match` expression (PHP 8.0+)

```php
// ⚠️ Поддерживается, но строго типизированное сравнение
$result = match($value) {
    1, 2    => 'low',
    default => 'high',
};
```

Следите за тем, чтобы типы ветвей совпадали — KPHP выводит тип `match` из всех ветвей сразу.

---

### Nullsafe operator `?->`

```php
// ⚠️ Поддерживается в новых версиях KPHP, но лучше явная проверка
$val = $obj?->getValue();
```

---

### `putenv()` / `$_ENV`

В `EnvLoader` используется `$_ENV[$name] = $value`. `putenv()` **не вызывается** намеренно — он может быть недоступен в некоторых KPHP-окружениях.

---

## Разрешённые конструкции (KPHP-friendly)

| Конструкция | Статус |
|---|---|
| `declare(strict_types=1)` | ✅ |
| `final class` | ✅ |
| `interface` | ✅ |
| `abstract class` | ✅ (с ограничениями) |
| Нативные типы PHP 8.0+ (union `int\|string`, `?type`) | ✅ |
| `array<K, V>` в PHPDoc | ✅ |
| `new ClassName()` (явный, не динамический) | ✅ |
| `try/catch/finally` с хотя бы одним `catch` | ✅ |
| `instanceof` | ✅ |
| `\DateTimeImmutable` | ✅ |
| `substr()`, `strpos()`, `strlen()` | ✅ |
| `explode()`, `implode()` | ✅ |
| `array_key_exists()`, `isset()` | ✅ |
| `sprintf()` | ✅ |
| `file()` (1 аргумент) | ✅ |
| `is_string()`, `is_int()`, `is_bool()`, `is_array()` | ✅ |

---

## Структура entrypoint для KPHP

KPHP **не поддерживает autoloading** (PSR-4, Composer). Все файлы должны быть включены явно через `require_once` в entrypoint:

```php
// build/kphp-entrypoint.php
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
```

> **Порядок важен:** базовые исключения и интерфейсы — раньше классов, которые их используют.

---

## Как проверить совместимость

```bash
# Собрать и проверить оба режима (KPHP binary + PHAR)
docker build -f Dockerfile.check -t lphenom-core-check .
```

Обе стадии должны завершиться с кодом 0. Если KPHP-стадия упала — ищите нарушение одного из правил выше.

---

## Ссылки

- [KPHP Documentation](https://vkcom.github.io/kphp/)
- [KPHP vs PHP differences](https://vkcom.github.io/kphp/kphp-language/kphp-vs-php/whats-the-difference.html)
- [KPHP Docker image](https://hub.docker.com/r/vkcom/kphp)
- [lphenom/core — Container](./container.md)
- [lphenom/core — Config & EnvLoader](./config.md)

