<?php

namespace Spewia\DependencyInjection;

use Spewia\DependencyInjection\Exception\ServiceNotFoundException;
use Spewia\DependencyInjection\Exception\CircularDependencyException;

/**
 * Container class to be able to use dependency injection.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
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
    protected $instances = array();

    /**
     * @var array
     */
    protected $loading = array();

    /**
     * @var array
     */
    protected $method_calls = array();

    /**
     * @var bool
     */
    protected $method_calls_running = false;

    /**
     * Builds a container object.
     *
     * @param array $configuration array which contains all the configuration. It has the following format:
     *  array(
     *      'IDENTIFIER' => 'FULL_CLASS_NAME',
     *      'constructor_parameters'    => {parameters_array}
     *      'configuration_calls'       => array(
     *          'METHOD'    => {parameters_array},
     *          ...
     *      ),
     *      ...
     * )
     *
     * where {parameters_array} is an array wich can contain references to services or constant values. The format to
     * each of them is the following:
     *
     * * Service:
     *   array(
     *      'type'  => 'service',
     *      'id'    => 'SERVICE_ID'
     *   )
     *
     * * Constant value:
     *   array(
     *      'type'  => 'constant',
     *      'value' => CONSTANT_VALUE
     *   )
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
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

    /**
     * Instantiates the class as defined by the given configuration.
     *
     * @param $configuration An element of the configuration
     * @return mixed
     */
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

    /**
     * Calls all the instantiated services method dependencies.
     */
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

    /**
     * Parses the input array, returning an array wich can be used with call_user_func_array().
     *
     * @param array $input_parameters
     * @return array
     */
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
