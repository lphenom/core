<?php

declare(strict_types=1);

namespace LPhenom\Core\Container;

/**
 * Reflection-free DI container.
 *
 * All dependencies are registered explicitly via set().
 * No dynamic class loading, no reflection, no magic — KPHP-compatible.
 *
 * KPHP compatibility notes:
 *  - Factories use ServiceFactoryInterface (callable not allowed in array<K,V> phpdocs)
 *  - get() returns mixed — use instance_cast() in KPHP to cast to concrete type
 *  - No try/finally (KPHP requires at least one catch)
 *  - No !isset()+throw pattern (KPHP parser limitation)
 */
final class Container
{
    /** @var array<string, ServiceFactoryInterface> */
    private array $factories = [];

    /** @var array<string, bool> */
    private array $shared = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    /**
     * Register a service factory.
     *
     * In PHP mode any callable wrapped in an anonymous class works:
     *
     *   $container->set('id', new class implements ServiceFactoryInterface {
     *       public function create(Container $c): MyService { return new MyService(); }
     *   });
     */
    public function set(string $id, ServiceFactoryInterface $factory, bool $shared = true): void
    {
        $this->factories[$id] = $factory;
        $this->shared[$id]    = $shared;
        unset($this->instances[$id]);
    }

    /**
     * Resolve a service by id.
     *
     * Returns mixed — in KPHP use instance_cast($container->get('id'), MyService::class).
     *
     * @return mixed
     * @throws ContainerException
     */
    public function get(string $id): mixed
    {
        $factory = $this->factories[$id] ?? null;

        if ($factory === null) {
            throw new ContainerException(sprintf(
                'Service "%s" is not registered in the container.',
                $id
            ));
        }

        $isShared = isset($this->shared[$id]) && $this->shared[$id];

        if ($isShared) {
            $cached = $this->instances[$id] ?? null;
            if ($cached !== null) {
                return $cached;
            }
        }

        if (isset($this->resolving[$id])) {
            throw new ContainerException(sprintf(
                'Circular dependency detected while resolving "%s".',
                $id
            ));
        }

        $this->resolving[$id] = true;

        $exception = null;
        $instance  = null;

        try {
            $result   = $factory->create($this);
            $instance = $result;
        } catch (\Throwable $e) {
            $exception = $e;
        }

        unset($this->resolving[$id]);

        if ($exception !== null) {
            throw $exception;
        }

        if ($isShared) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Check whether a service is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->factories[$id]);
    }
}
