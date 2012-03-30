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

        $this->response = $this->container->get('factory.response')->build();

        $this->template = $this->container->get('template');

        $this->initialize();
    }

    /**
     * Function to allow all the controllers to customize what to do when the __construct is finished.
     */
    protected function initialize()
    {

    }

    /**
     * (non-PHPdoc)
     * @see Spewia\Controller.ControllerInterface::render()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render()
    {
        $this->response->setContent($this->template->render());
        return $this->response;
    }
}