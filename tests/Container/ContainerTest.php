<?php

declare(strict_types=1);

namespace LPhenom\Core\Tests\Container;

use LPhenom\Core\Container\Container;
use LPhenom\Core\Container\ContainerException;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testSetAndGetReturnsObject(): void
    {
        $container = new Container();
        $container->set('service', fn (Container $c): object => new \stdClass());

        $result = $container->get('service');
        self::assertInstanceOf(\stdClass::class, $result);
    }

    public function testSharedReturnsSameInstance(): void
    {
        $container = new Container();
        $container->set('service', fn (Container $c): object => new \stdClass(), true);

        $first  = $container->get('service');
        $second = $container->get('service');

        self::assertSame($first, $second);
    }

    public function testNotSharedReturnsDifferentInstances(): void
    {
        $container = new Container();
        $container->set('service', fn (Container $c): object => new \stdClass(), false);

        $first  = $container->get('service');
        $second = $container->get('service');

        self::assertNotSame($first, $second);
    }

    public function testHasReturnsTrueForRegistered(): void
    {
        $container = new Container();
        $container->set('service', fn (Container $c): object => new \stdClass());

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

        $container->set('a', function (Container $c): object {
            return $c->get('b');
        });

        $container->set('b', function (Container $c): object {
            return $c->get('a');
        });

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageMatches('/[Cc]ircular/');
        $container->get('a');
    }

    public function testRebindingResetsSharedInstance(): void
    {
        $container = new Container();

        $counter = 0;
        $container->set('service', function (Container $c) use (&$counter): object {
            $counter++;
            $obj = new \stdClass();
            $obj->count = $counter;
            return $obj;
        }, true);

        $first = $container->get('service');
        /** @var \stdClass $first */
        self::assertSame(1, $first->count);

        // Re-register the same service
        $container->set('service', function (Container $c) use (&$counter): object {
            $counter++;
            $obj = new \stdClass();
            $obj->count = $counter;
            return $obj;
        }, true);

        $second = $container->get('service');
        /** @var \stdClass $second */
        self::assertSame(2, $second->count);
    }

    public function testServiceCanResolveAnotherService(): void
    {
        $container = new Container();

        $dep = new \stdClass();
        $dep->value = 'dependency';

        $container->set('dep', fn (Container $c): object => $dep);
        $container->set('main', function (Container $c): object {
            $obj = new \stdClass();
            $obj->dep = $c->get('dep');
            return $obj;
        });

        $main = $container->get('main');
        self::assertInstanceOf(\stdClass::class, $main);
        self::assertSame($dep, $main->dep);
    }
}
