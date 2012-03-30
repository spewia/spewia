<?php

namespace Spewia\Controller\Factory;

use Spewia\Factory\FactoryInterface;
use Spewia\DependencyInjection\ContainerInterface;
use Spewia\Controller\Factory\Exception\InvalidClassException;
use Spewia\Controller\Factory\Exception\ClassNotSpecifiedException;
/**
 * Created by JetBrains PhpStorm.
 * User: rllopart
 * Date: 30/03/12
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */
class ControllerFactory implements FactoryInterface
{
    public function __construct(ContainerInterface $container)
    {}

    /**
     * Builds the given controller, injecting it the Container.
     *
     * @param array $options An array which must have a 'class' key, whose value is the class name of the controller
     *                       to instantiate.
     *
     * @return \Spewia\Controller\ControllerInterface The required controller.
     *
     * @throws ClassNotSpecifiedException Thrown when the class key of the array hasn't been defined.
     * @throws InvalidClassException      Thrown when the instantiated class doesn't implement the expected interface.
     */
    public function build(array $options = array())
    {
        // TODO: Implement build() method.
    }
}
