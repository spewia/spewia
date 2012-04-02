<?php

namespace Spewia\Dispatcher;

use Spewia\DependencyInjection\Container;
use Spewia\Dispatcher\Exception\FileNotFoundException;
/**
 * Implementation of the dispatcher interface, wich handles a request received by the webserver.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $project_configuration;

    function __construct()
    {
        // Load the basic configuration.
        $this->project_configuration = include __DIR__ . '/../config/project_configuration.php';
        $dic_configuration = include __DIR__ . '/../config/dic_configuration.php';

        // Create the container
        $this->container = $this->createDependencyInjectionContainer($dic_configuration);
    }

    /**
     * Run the Dispatcher for the recieved Request to the webserver.
     */
    public function run()
    {
        $router = $this->container->get('router');

        $routing_info = $router->parseRequest($this->container->get('request'));

        $controller = $this->container->get('factory.controller')->build(array(
            'class' => $routing_info['controller']
        ));

        call_user_func_array(array($controller, $routing_info['action'] . 'Action'), $routing_info['params']);
        $controller->render()->send();
    }

    /**
     * Configurates all the systems SetUp by the Dispatcher with the files in the $configuration_dir directory.
     *
     * @param string $configuration_dir
     *
     * @throws \Spewia\Dispatcher\Exception\FileNotFoundException If a required file isn't found.
     *
     * @return DispatcherInterface Reference to the Dispatcher object itself.
     */
    public function configure($configuration_dir)
    {
        $dic_configuration_file = $configuration_dir . '/dic_configuration.php';

        if(!file_exists($dic_configuration_file)) {
            throw new FileNotFoundException('The dependency injection configuration file couldn\'t be found in "'.
                $dic_configuration_file .'".');
        }

        $this->container->addServiceConfigurations(include $dic_configuration_file);

        $project_configuration_file = $configuration_dir . '/project_configuration.php';

        if(file_exists($project_configuration_file)) {
            $this->project_configuration = include $project_configuration_file + $this->project_configuration;
        }

        return $this;
    }

    /**
     * Creates the Dependency Injection Container and returns it.
     *
     * @param array $configuration The base configuration.
     *
     * @return \Spewia\DependencyInjection\Container
     */
    protected function createDependencyInjectionContainer(array $configuration)
    {
        // TODO: Load framework base configuration.
        return new Container($configuration);
    }
}