<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\Container;

use LPhenom\Core\Container\Container;
use LPhenom\Core\Container\ContainerException;
use LPhenom\Core\Container\ServiceFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Helper: create an anonymous ServiceFactoryInterface from a closure.
 *
 * @param \Closure(Container): mixed $fn
 */
function makeFactory(\Closure $fn): ServiceFactoryInterface
{
    return new class ($fn) implements ServiceFactoryInterface {
        private \Closure $fn;

        public function __construct(\Closure $fn)
        {
            $this->fn = $fn;
        }

        public function create(Container $container): mixed
        {
            return ($this->fn)($container);
        }
    };
}

final class ContainerTest extends TestCase
{
    public function testSetAndGetReturnsObject(): void
    {
        $container = new Container();
        $container->set('service', makeFactory(fn (Container $c): \stdClass => new \stdClass()));

        $result = $container->get('service');
        self::assertInstanceOf(\stdClass::class, $result);
    }

    public function testSharedReturnsSameInstance(): void
    {
        $container = new Container();
        $container->set('service', makeFactory(fn (Container $c): \stdClass => new \stdClass()), true);

        $first  = $container->get('service');
        $second = $container->get('service');

        self::assertSame($first, $second);
    }

    public function testNotSharedReturnsDifferentInstances(): void
    {
        $container = new Container();
        $container->set('service', makeFactory(fn (Container $c): \stdClass => new \stdClass()), false);

        $first  = $container->get('service');
        $second = $container->get('service');

        self::assertNotSame($first, $second);
    }

    public function testHasReturnsTrueForRegistered(): void
    {
        $container = new Container();
        $container->set('service', makeFactory(fn (Container $c): \stdClass => new \stdClass()));

        self::assertTrue($container->has('service'));
    }

    public function testHasReturnsFalseForUnregistered(): void
    {
        $container = new Container();
        self::assertFalse($container->has('unknown'));
    }

    public function testGetThrowsForUnregisteredService(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageMatches('/not registered/');
        $container->get('unknown');
    }

    public function testCircularDependencyThrowsException(): void
    {
        $container = new Container();

        $container->set('a', makeFactory(function (Container $c): \stdClass {
            /** @var \stdClass */
            return $c->get('b');
        }));

        $container->set('b', makeFactory(function (Container $c): \stdClass {
            /** @var \stdClass */
            return $c->get('a');
        }));

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageMatches('/[Cc]ircular/');
        $container->get('a');
    }

    public function testRebindingResetsSharedInstance(): void
    {
        $container = new Container();
        $counter   = 0;

        $makeCountingFactory = function () use (&$counter): ServiceFactoryInterface {
            return makeFactory(function (Container $c) use (&$counter): \stdClass {
                $counter++;
                $obj        = new \stdClass();
                $obj->count = $counter;
                return $obj;
            });
        };

        $container->set('service', $makeCountingFactory(), true);
        /** @var \stdClass $first */
        $first = $container->get('service');
        self::assertSame(1, $first->count);

        // Re-register — shared cache must be cleared
        $container->set('service', $makeCountingFactory(), true);
        /** @var \stdClass $second */
        $second = $container->get('service');
        self::assertSame(2, $second->count);
    }

    public function testServiceCanResolveAnotherService(): void
    {
        $container = new Container();

        $dep        = new \stdClass();
        $dep->value = 'dependency';

        $container->set('dep', makeFactory(fn (Container $c): \stdClass => $dep));
        $container->set('main', makeFactory(function (Container $c): \stdClass {
            $obj      = new \stdClass();
            $obj->dep = $c->get('dep');
            return $obj;
        }));

        /** @var \stdClass $main */
        $main = $container->get('main');
        self::assertInstanceOf(\stdClass::class, $main);
        self::assertSame($dep, $main->dep);
    }
}
