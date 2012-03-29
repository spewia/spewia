<?php

namespace Spewia\DependencyInjection;

use Spewia\DependencyInjection\Exception\ServiceNotFoundException;
use Spewia\DependencyInjection\Exception\CircularDependencyException;

/**
 * Container class to be able to use dependency injection.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
 * @todo: Update the documentation with information of the support of factory calls.
 */
class Container implements ContainerInterface
{
    /**
     * The configuration given.
     *
     * @var array
     */
    protected $configuration;

    /**
     * Instances of the objects wich have already been served.
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Array of classes wich are being loaded.
     *
     * @var array
     */
    protected $loading = array();

    /**
     * Array of classes wich have pending method calls.
     *
     * @var array
     */
    protected $method_calls = array();

    /**
     * Flag to mark if the container is processing the method calls of the given class.
     *
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
     * 'constructor_parameters' and 'configuration_calls' are optional.
     *
     * Instead of 'constructor_parameters', a "factory" key can be defined, wich will call a factory instead of using
     * the "new" keyword. The factory key has the following structure.
     *
     * array(
     *      'class'     => 'FACTORY_CLASS',
     *      'service'   => 'FACTORY_SERVICE',
     *      'method'    => 'FACTORY_METHOD',
     *      'params'    => {parameters_array}
     * )
     *
     * Where you can only use either a 'class' or a 'service' key and the 'params' key is optional. If you use the
     * 'class' key, it should be the full class name of a class and it'll do a static call to method, and if you use
     * 'service' it'll call the given 'method' of the given service id.
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
    public function __construct(array $configuration = array())
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
        if($identifier == 'container') {
            return $this;
        }

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
     * Adds the given array of service configurations to the one given to the constructor.
     *
     * @param array $service_configurations
     */
    public function addServiceConfigurations(array $service_configurations)
    {
        $keys_to_remove = array_intersect(array_keys($service_configurations), array_keys($this->instances));

        foreach($keys_to_remove as $key) {
            unset($this->instances[$key]);
        }

        /* The order in this union is important. This way, the keys wich are shared in both arrays are taken from
         * $service_configurations instead of $this->configuration.
         */
        $this->configuration = $service_configurations + $this->configuration;
    }

    /**
     * Instantiates the class as defined by the given configuration.
     *
     * @param array $configuration An element of the configuration
     * @return mixed
     */
    protected function instantiateClass($configuration)
    {
        if(array_key_exists('factory', $configuration)) {
            $params = array();

            if(array_key_exists('params', $configuration['factory'])) {
                $params = $this->parseParameters($configuration['factory']['params']);
            }

            if(array_key_exists('class', $configuration['factory'])) {
                $called_object = $configuration['factory']['class'];
            } else {
                $called_object = $this->get($configuration['factory']['service']);
            }

            return call_user_func_array(
                array($called_object, $configuration['factory']['method']),
                $params
            );
        }

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
