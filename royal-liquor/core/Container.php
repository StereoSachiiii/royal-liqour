<?php

declare(strict_types=1);

namespace Core;

use Exception;

/**
 * Dependency Injection Container
 * 
 * Manages object creation and dependency resolution
 * Supports lazy instantiation and singleton patterns
 */
class Container
{
    /**
     * @var array<string, callable> Service factories
     */
    private array $services = [];

    /**
     * @var array<string, object> Singleton instances
     */
    private array $singletons = [];

    /**
     * @var array<string, bool> Track which services are singletons
     */
    private array $isSingleton = [];

    /**
     * Register a service factory
     *
     * @param string $id Service identifier (usually class name)
     * @param callable $factory Factory function that creates the service
     * @return void
     */
    public function set(string $id, callable $factory): void
    {
        $this->services[$id] = $factory;
        $this->isSingleton[$id] = false;
    }

    /**
     * Register a singleton service
     * Singleton instances are created once and reused
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function
     * @return void
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->services[$id] = $factory;
        $this->isSingleton[$id] = true;
    }

    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return object The service instance
     * @throws Exception If service not found
     */
    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new Exception("Service '{$id}' not found in container");
        }

        // Return existing singleton instance if available
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        // Create new instance
        $factory = $this->services[$id];
        $instance = $factory($this);

        // Store singleton instance
        if ($this->isSingleton[$id]) {
            $this->singletons[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * Register an existing instance as a singleton
     *
     * @param string $id Service identifier
     * @param object $instance The instance to register
     * @return void
     */
    public function instance(string $id, object $instance): void
    {
        $this->singletons[$id] = $instance;
        $this->isSingleton[$id] = true;
        
        // Create a factory that returns the instance
        $this->services[$id] = fn() => $instance;
    }

    /**
     * Make an instance of a class with dependency resolution
     * Useful for creating objects on-demand
     *
     * @param string $className Class name to instantiate
     * @return object
     * @throws Exception If class doesn't exist
     */
    public function make(string $className): object
    {
        if (!class_exists($className)) {
            throw new Exception("Class '{$className}' not found");
        }

        // If already registered, use get()
        if ($this->has($className)) {
            return $this->get($className);
        }

        // Simple instantiation (can be enhanced with reflection for auto-wiring)
        return new $className();
    }

    /**
     * Clear all singletons (useful for testing)
     *
     * @return void
     */
    public function clearSingletons(): void
    {
        $this->singletons = [];
    }

    /**
     * Clear all services (useful for testing)
     *
     * @return void
     */
    public function clear(): void
    {
        $this->services = [];
        $this->singletons = [];
        $this->isSingleton = [];
    }
}
