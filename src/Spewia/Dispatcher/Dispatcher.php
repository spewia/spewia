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
    function __construct()
    {
        $this->container = $this->createDependencyInjectionContainer();
    }

    /**
     * Run the Dispatcher for the recieved Request to the webserver.
     */
    public function run()
    {
        // TODO: Implement run() method.
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
    }

    /**
     * Creates the Dependency Injection Container and returns it.
     *
     * @return \Spewia\DependencyInjection\Container
     */
    protected function createDependencyInjectionContainer()
    {
        // TODO: Load framework base configuration.
        return new Container();
    }
}
