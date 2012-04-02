<?php

namespace Spewia\Dispatcher;

interface DispatcherInterface
{
    /**
     * Run the Dispatcher for the recieved Request to the webserver.
     */
    public function run();

    /**
     * Configurates all the systems SetUp by the Dispatcher with the files in the $configuration_dir directory.
     *
     * @param string $configuration_dir
     *
     * @throws \Spewia\Dispatcher\Exception\FileNotFoundException If a required file isn't found.
     *
     * @return DispatcherInterface Reference to the Dispatcher object itself.
     */
    public function configure($configuration_dir);
}
