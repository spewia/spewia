<?php

namespace Spewia\Controller;

use Spewia\DependencyInjection\ContainerInterface;
use Spewia\Template\TemplateInterface;
use Symfony\Component\HttpFoundation\Response;

class BaseController implements ControllerInterface
{
    protected $container;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var \Spewia\Template\TemplateInterface
     */
    protected $template;

    public final function __construct(ContainerInterface $container)
    {

        $this->container = $container;

        $this->response = new Response();

        $this->initialize();
    }

    /**
     * Function to allow all the controllers to customize what to do when the __construct is finished.
     */
    public function initialize()
    {

    }

    /**
     * (non-PHPdoc)
     * @see Spewia\Controller.ControllerInterface::render()
     */
    public function render()
    {

    }
}