<?php

namespace Spewia\Controller;

class BaseController implements ControllerInterface
{
    /**
     * Array containing all the needed information to construct the Response to send to the server.
     * @var array
     */
    protected $response;

    /**
     * Array containing all the needed data to construct the View that will be shown to the user.
     * @var array
     */
    protected $template;

    /**
     * (non-PHPdoc)
     * @see Spewia\Controller.ControllerInterface::render()
     */
    public function render()
    {

    }
}