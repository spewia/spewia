<?php

namespace Spewia\Dispatcher;

use Spewia\DependencyInjection\Container;
use Spewia\Dispatcher\Exception\FileNotFoundException;
use Spewia\Router\Exception\RouteNotFoundException;
use Spewia\Dispatcher\Exception\DispatcherException;
use Spewia\Factory\FactoryInterface;
/**
 * Implementation of the dispatcher interface, wich handles a request received by the webserver.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * The container which will be used by the application.
     *
     * @var Container
     */
    protected $container;

    /**
     * The project configuration array. It currently supports only one key, 'error_controller', which is the class which
     * is the controller that will be called if there is an error in the application.
     *
     * @var array
     */
    protected $project_configuration;

    /**
     * Builds the dispatcher, reading the files from the framework configuration directory.
     */
    function __construct()
    {
        // Load the basic configuration.
        $this->project_configuration = include __DIR__ . '/../config/project_configuration.php';
        $dic_configuration = include __DIR__ . '/../config/dic_configuration.php';

        // Create the container
        $this->container = $this->createDependencyInjectionContainer($dic_configuration);
    }

    /**
     * Run the Dispatcher for the received Request to the webserver.
     */
    public function run()
    {
        $router = $this->container->get('router');
        $controller_factory = $this->container->get('factory.controller');

        try {

            $routing_info = $router->parseRequest($this->container->get('request'));

            $controller = $controller_factory->build(array(
                'class' => $routing_info['controller']
            ));

            $action_to_call = array($controller, $routing_info['action'] . 'Action');

            if(!is_callable($action_to_call)) {
                throw new DispatcherException("The given action ({$routing_info['controller']}::{$action_to_call[1]})"
                    . "  can't be called.");
            }

            call_user_func_array($action_to_call, $routing_info['params']);
            $controller->render()->send();

        } catch (RouteNotFoundException $e ) {
            $this->handleError($controller_factory, $e, 'error404Action');
        } catch (\Exception $e ) {
            $this->handleError($controller_factory, $e, 'error5xxAction');
        }
    }

    /**
     * Configures all the systems SetUp by the Dispatcher with the files in the $configuration_dir directory.
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
     * Handles an error, by calling the given action from the 'error_controller'.
     *
     * @param \Spewia\Factory\FactoryInterface $controller_factory
     * @param \Exception $exception
     * @param $action
     */
    protected function handleError(FactoryInterface $controller_factory, \Exception $exception, $action)
    {
        $controller = $controller_factory->build(array(
            'class' => $this->project_configuration['error_controller']
        ));

        $controller->$action($exception);
        $controller->render()->send();
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
        return new Container($configuration);
    }
}
