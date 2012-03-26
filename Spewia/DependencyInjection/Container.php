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
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $instances;

    /**
     * @var array
     */
    protected $loading;

    /**
     * @var array
     */
    protected $method_calls;

    protected $method_calls_running;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;

        $this->loading = array();
        $this->instances = array();

        $this->method_calls = array();

        $this->method_calls_running = false;
    }

    /**
     * * Returns an object for a given identifier.
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
        if(!array_key_exists($identifier, $this->instances)) {
            if(!array_key_exists($identifier, $this->configuration)) {
                throw new ServiceNotFoundException;
            }

            if(array_search($identifier, $this->loading)) {
                throw new CircularDependencyException;
            }

            array_push($this->loading, $identifier);

            if(array_key_exists('configuration_calls', $this->configuration[$identifier])) {
                $this->method_calls[$identifier] = $this->configuration[$identifier]['configuration_calls'];
            }

            $this->instances[$identifier] =
                $this->instantiateClass($this->configuration[$identifier]);

            array_pop($this->loading);

            if(empty($this->loading)) {
                $this->callServiceMethodDependencies();
            }
        }

        return $this->instances[$identifier];
    }

    protected function instantiateClass($configuration)
    {
        if(method_exists($configuration['class'], '__construct') &&
            array_key_exists('constructor_parameters', $configuration)) {

            $params = $this->parseParameters($configuration['constructor_parameters']);

            $reflection = new \ReflectionClass($configuration['class']);

            return $reflection->newInstanceArgs($params);
        }
        return new $configuration['class'];
    }

    protected function callServiceMethodDependencies()
    {
        if($this->method_calls_running) {
            return;
        }
        $this->method_calls_running = true;

        while(!empty($this->method_calls)) {
            $service = array_rand($this->method_calls);

            foreach($this->method_calls[$service] as $method => $parameters) {
                $parameters = $this->parseParameters($parameters);

                call_user_func_array(array($this->get($service), $method), $parameters);
            }

            unset($this->method_calls[$service]);
        }

        $this->method_calls_running = false;
    }

    protected function parseParameters(array $input_parameters)
    {
        $output_parameters = array();

        foreach($input_parameters as $parameter) {
            switch($parameter['type']) {
                case 'service':
                    $output_parameters[] = $this->get($parameter['id']);
                    break;
                case 'constant':
                    $output_parameters[] = $parameter['value'];
                    break;
            }
        }

        return $output_parameters;
    }
}
