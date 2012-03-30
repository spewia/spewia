<?php

namespace Tests\Spewia\Controller\Factory;

use Spewia\Controller\Factory\ControllerFactory;
/**
 * Test of the ControllerFactory.
 */
class ControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Spewia\Controller\Factory\ControllerFactory
     */
    protected $object;
    protected $container_mock;

    public function setUp()
    {
        $this->container_mock = \Mockery::mock('Spewia\DependencyInjection\ContainerInterface');

        $this->object = new ControllerFactory($this->container_mock);
    }

    /**
     * Tests the standard behaviour of the factory.
     */
    public function testBuild()
    {
        $controller = $this->object->build(array(
            'class' => '\Tests\Spewia\Controller\Factory\DummyController'
        ));

        $this->assertInstanceOf('\Tests\Spewia\Controller\Factory\DummyController', $controller,
            'The controller returned should be an instance of DummyController.');

        $this->assertSame($this->container_mock, $controller->getContainer(),
            'The factory should inject the dependency it was injected.');
    }

    /**
     * Tests the factory behaviour when given an array missin the 'class' key.
     *
     * @expectedException \Spewia\Controller\Factory\Exception\ClassNotSpecifiedException
     */
    public function testBuildWithoutClass()
    {
        $controller = $this->object->build(array());
    }

    /**
     * Tests the factory behaviour when given a class which isn't a controller.
     *
     * @expectedException \Spewia\Controller\Factory\Exception\InvalidClassException
     */
    public function testBuildInvalidClass()
    {
        $object = $this->object->build(array(
            'class' => '\stdClass'
        ));
    }
}

class DummyController implements \Spewia\Controller\ControllerInterface
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function render()
    {
        // Dummy method.
    }
}
