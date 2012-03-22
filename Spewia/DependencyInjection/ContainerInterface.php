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
     * @return mixed
     * @throws Spewia\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function get($identifier);
}
