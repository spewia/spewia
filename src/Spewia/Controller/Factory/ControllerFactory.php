<?php

namespace Spewia\Controller\Factory;

use Spewia\Factory\FactoryInterface;
use Spewia\DependencyInjection\ContainerInterface;
use Spewia\Controller\ControllerInterface;
use Spewia\Controller\Factory\Exception\InvalidClassException;
use Spewia\Controller\Factory\Exception\ClassNotSpecifiedException;
/**
 * Class used to create the controllers.
 */
class ControllerFactory implements FactoryInterface
{
    /**
     * The container which should be injected into the controllers.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Builds the ControllerFactory, with a container parameter wich will be injected in all the created controllers.
     *
     * @param \Spewia\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Builds the given controller, injecting it the Container.
     *
     * @param array $options An array which must have a 'class' key, whose value is the class name of the controller
     *                       to instantiate.
     *
     * @return ControllerInterface The required controller.
     *
     * @throws ClassNotSpecifiedException Thrown when the class key of the array hasn't been defined.
     * @throws InvalidClassException      Thrown when the instantiated class doesn't implement the expected interface.
     */
    public function build(array $options = array())
    {
        if(!array_key_exists('class', $options)) {
            throw new ClassNotSpecifiedException;
        }

        $controller_class = $options['class'];

        $controller = new $controller_class($this->container);

        if($controller instanceof ControllerInterface) {
            return $controller;
        }

        throw new InvalidClassException;
    }
}
