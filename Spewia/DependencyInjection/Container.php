<?php

namespace Spewia\DependencyInjection;

use Spewia\DependencyInjection\Exception\ServiceNotFoundException;
use Spewia\DependencyInjection\Exception\CircularDependencyException;
/**
 * Created by JetBrains PhpStorm.
 * User: rllopart
 * Date: 23/03/12
 * Time: 10:04
 * To change this template use File | Settings | File Templates.
 */
class Container implements ContainerInterface
{
    protected $configuration;
    protected $instances;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * * Returns an object for a given identifier.
     *
     * @todo: Add the ability to inject parameters using setter methods.
     *
     * @param string $identifier
     *
     * @return mixed The service identified by $identifier.
     *
     * @throws \Spewia\DependencyInjection\Exception\ServiceNotFoundException When the service hasn't been defined.
     * @throws \Spewia\DependencyInjection\Exception\CircularDependencyException When the service depends on services
     * wich depend in the service itself.
     */
    public function get($identifier)
    {
        if(!$this->instances[$identifier]) {
            if(!array_key_exists($identifier, $this->configuration)) {
                throw new ServiceNotFoundException;
            }

            $this->instances[$identifier] =
                $this->instantiateClass($this->configuration[$identifier]);
        }

        return $this->instances[$identifier];
    }

    protected function instantiateClass($configuration)
    {
        if(method_exists($configuration['class'], '__construct') &&
            array_key_exists('constructor_parameters', $configuration)) {
            $params = array();

            foreach($configuration['constructor_parameters'] as $constructor_paramenter) {
                switch($constructor_paramenter['type']) {
                    case 'service':
                        $params[] = $this->get($constructor_paramenter['id']);
                        break;
                    case 'constant':
                        $params[] = $constructor_paramenter['value'];
                        break;
                }
            }
            $reflection = new \ReflectionClass($configuration['class']);
            return $reflection->newInstanceArgs($params);
        }
        return new $configuration['class'];
    }
}
