<?php

namespace Tests\Spewia\Dispatcher;

use Spewia\Dispatcher\Dispatcher as BaseDispatcher;
use org\bovigo\vfs\vfsStream;
/**
 * Created by JetBrains PhpStorm.
 * User: Lumbendil
 * Date: 29/03/12
 * Time: 10:50
 * To change this template use File | Settings | File Templates.
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dispatcher
     */
    protected $object;
    protected $folder;
    protected $container;
    protected $configuration_directory;

    public function setUp()
    {
        $this->object = new Dispatcher();
        $this->folder = vfsStream::setup('root');

        global $mock;
        $this->container = $mock;

        $this->container->shouldIgnoreMissing();

        $this->configuration_directory = vfsStream::url('root');
    }

    public function testConfigure()
    {
        $this->createRequiredConfigurationFiles();

        $this->container->shouldReceive('addServiceConfigurations')
            ->once()
            ->with(include vfsStream::url('root/dic_configuration.php'));

        $return = $this->object->configure($this->configuration_directory);

        $this->assertSame($this->object, $return,
            'The configure call should return the Dispatcher itself.');
    }

    /**
     * @expectedException \Spewia\Dispatcher\Exception\FileNotFoundException
     */
    public function testConfigureMissingFile()
    {
        $this->object->configure($this->configuration_directory);
    }

    public function testRun()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];

        $controller = \Mockery::mock('Spewia\Controller\ControllerInterface');

        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andReturn(
            array(
                'controller' => '\DummyController',
                'action'     => 'show',
                'params'     => array()
            )
        );

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\DummyController'
            ))
            ->andReturn($controller);

        $controller->shouldReceive('showAction')
            ->once()
            ->withNoArgs();

        $controller->shouldReceive('render')
            ->once()
            ->andReturn($response);

        $response->shouldReceive('send')
            ->once();

        $this->object->configure($this->configuration_directory)->run();
    }

    public function testRunRouteNotFound()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];
        $error_controller = \Mockery::mock('Spewia\Controller\ErrorController');
        $route_not_found_exception = new \Spewia\Router\Exception\RouteNotFoundException();


        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andThrow($route_not_found_exception);

        $controller_factory->shouldReceive('build')
            ->with(array(
                'class' => '\Spewia\Controller\ErrorController'
            ))
            ->andReturn($error_controller);

        $error_controller->shouldReceive('error404Action')
            ->with($route_not_found_exception);

        $error_controller->shouldReceive('render')
            ->andReturn($response);

        $response->shouldReceive('send');

        $this->object->configure($this->configuration_directory)->run();
    }

    public function testRunControllerNotFound()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];
        $error_controller = \Mockery::mock('Spewia\Controller\ErrorController');
        $controller_not_found_exception = \Mockery::mock(); // TODO: Add the exception

        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andReturn(
            array(
                'controller' => '\DummyController',
                'action'     => 'show',
                'params'     => array()
            )
        );

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\DummyController'
        ))
            ->andThrow($controller_not_found_exception);

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\Spewia\Controller\ErrorController'
        ))
            ->andReturn($error_controller);

        $error_controller->shouldReceive('error5xxAction')
            ->with($controller_not_found_exception);

        $error_controller->shouldReceive('render')
            ->andReturn($response);

        $response->shouldReceive('send');

        $this->object->configure($this->configuration_directory)->run();
    }

    public function testActionDoesntExist()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];
        $controller = new FakeController();
        $error_controller = \Mockery::mock('Spewia\Controller\ErrorController');

        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andReturn(
            array(
                'controller' => '\Tests\Spewia\Dispatcher\FakeController',
                'action'     => 'show',
                'params'     => array()
            )
        );

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\Tests\Spewia\Dispatcher\FakeController'
        ))
            ->andReturn($controller);

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\Spewia\Controller\ErrorController'
        ))
            ->andReturn($error_controller);

        $error_controller->shouldReceive('error5xxAction')
            ->with(\Mockery::type('\Spewia\Dispatcher\Exception\DispatcherException'));

        $error_controller->shouldReceive('render')
            ->andReturn($response);

        $response->shouldReceive('send');

        $this->object->configure($this->configuration_directory)->run();
    }


    /**
     * Creates the configuration files required by the dispatcher.
     */
    protected function createRequiredConfigurationFiles()
    {
        $dic_configuration_file = vfsStream::newFile('dic_configuration.php');

        $this->folder->addChild($dic_configuration_file);

        $dic_configuration = <<<DIC_CONFIGURATION
<?php
return array(
    'router' => array(
        'class'                     => '\Spewia\Router\Router',
        'constructor_parameters'    => array(
            array(
                'type'  => 'constant',
                'value' => array(
                    'test' => array(
                        'pattern'       => '/test/test',
                        'controller'    => 'test',
                        'action'        => 'test',
                    )
                )
            )
        )
    )
);
DIC_CONFIGURATION;

        $dic_configuration_file->setContent($dic_configuration);
    }

    protected function prepareBasicMocks()
    {
        $router = \Mockery::mock('Spewia\Router\RouterInterface');
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $controller_factory = \Mockery::mock('Spewia\Controller\Factory\ControllerFactory');
        $response = \Mockery::mock('Spewia\Response\Response');

        $this->container->shouldReceive('get')
            ->with('router')
            ->andReturn($router);

        $this->container->shouldReceive('get')
            ->with('request')
            ->andReturn($request);

        $this->container->shouldReceive('get')
            ->with('factory.controller')
            ->andReturn($controller_factory);

        return compact('router', 'request', 'controller_factory', 'response');
    }
}

class Dispatcher extends BaseDispatcher
{
    protected function createDependencyInjectionContainer(array $configuration = array())
    {
        global $mock;
        $mock = \Mockery::mock('\Spewia\DependencyInjector\Container');
        return $mock;
    }
}

class FakeController implements \Spewia\Controller\ControllerInterface
{
    protected $render_called = false;

    public function render()
    {
        $this->render_called = true;
    }

    public function wasRenderCalled()
    {
        return $this->render_called;
    }
}
