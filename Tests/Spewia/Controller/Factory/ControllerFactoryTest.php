<?php

namespace Tests\Spewia\Controller\Factory;

use Spewia\Controller\Factory\ControllerFactory;
/**
 * Created by JetBrains PhpStorm.
 * User: rllopart
 * Date: 30/03/12
 * Time: 11:20
 * To change this template use File | Settings | File Templates.
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
     * @expectedException \Spewia\Controller\Factory\Exception\ClassNotSpecifiedException
     */
    public function testBuildWithoutClass()
    {
        $controller = $this->object->build(array());
    }

    /**
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
