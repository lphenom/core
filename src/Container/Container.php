<?php

declare(strict_types=1);

namespace LPhenom\Core\Container;

/**
 * Reflection-free DI container.
 *
 * All dependencies are registered explicitly via set().
 * No dynamic class loading, no reflection, no magic — KPHP-compatible.
 */
final class Container
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, bool> */
    private array $shared = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, bool> — tracks currently resolving services to detect circular deps */
    private array $resolving = [];

    /**
     * Register a service factory.
     *
     * @param callable(Container): object $factory
     */
    public function set(string $id, callable $factory, bool $shared = true): void
    {
        $this->bindings[$id] = $factory;
        $this->shared[$id]   = $shared;

        // Reset cached instance when re-binding
        unset($this->instances[$id]);
    }

    /**
     * Resolve a service by id.
     *
     * @throws ContainerException if the service is not registered or a circular dependency is detected.
     */
    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new ContainerException(sprintf(
                'Service "%s" is not registered in the container.',
                $id
            ));
        }

        if (isset($this->shared[$id]) && $this->shared[$id] && isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->resolving[$id])) {
            throw new ContainerException(sprintf(
                'Circular dependency detected while resolving "%s".',
                $id
            ));
        }

        $this->resolving[$id] = true;

        try {
            $instance = ($this->bindings[$id])($this);
        } finally {
            unset($this->resolving[$id]);
        }

        if (!is_object($instance)) {
            throw new ContainerException(sprintf(
                'Factory for "%s" must return an object.',
                $id
            ));
        }

        if (isset($this->shared[$id]) && $this->shared[$id]) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Check whether a service is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }
}
