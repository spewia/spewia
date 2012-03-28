<?php

namespace Spewia\DependencyInjection;
/**
 * Interface to be implemented by any class which wishes to act as a Dependency Injection Container.
 */
interface ContainerInterface
{
    /**
     * Returns an object for a given identifier.
     *
     * @param string $identifier
     *
     * @return mixed The service identified by $identifier.
     *
     * @throws \Spewia\DependencyInjection\Exception\ServiceNotFoundException When the service hasn't been defined.
     * @throws \Spewia\DependencyInjection\Exception\CircularDependencyException When the service depends on services
     * wich depend in the service itself.
     */
    public function get($identifier);
}
