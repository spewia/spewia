<?php

namespace Spewia\Dispatcher;
/**
 * Implementation of the dispatcher interface, wich handles a request received by the webserver.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{
    function __construct()
    {
        // TODO: Implement __construct() method.
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
        // TODO: Implement configure() method.
    }
}
