<?php

declare(strict_types=1);

namespace LPhenom\Core\Container;

/**
 * Factory interface for Container service registration.
 *
 * KPHP-compatible: uses a concrete interface instead of callable,
 * because KPHP does not support 'callable' inside array<K,V> phpdoc types.
 *
 * The return type is intentionally untyped (inferred by KPHP from concrete implementation).
 * In PHP 8.1+ mode the return type is mixed.
 *
 * Usage (PHP + KPHP compatible):
 *
 *   $container->set('my_service', new class implements ServiceFactoryInterface {
 *       public function create(Container $container): MyService {
 *           return new MyService($container->get('dep'));
 *       }
 *   });
 */
interface ServiceFactoryInterface
{
    /**
     * Create and return the service instance.
     *
     * Implement with a concrete return type in each factory class — KPHP will
     * infer the actual type from the implementation, not from this interface.
     *
     * @return mixed
     */
    public function create(Container $container): mixed;
}
