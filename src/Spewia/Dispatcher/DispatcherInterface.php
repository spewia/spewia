<?php

namespace Spewia\Dispatcher;

interface DispatcherInterface
{
    /**
     * Run the Dispatcher for the recieved Request to the webserver.
     */
    public function run();

}
