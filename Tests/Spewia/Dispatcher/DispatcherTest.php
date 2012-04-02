<?php

namespace Tests\Spewia\Dispatcher;

use Spewia\Dispatcher\Dispatcher as BaseDispatcher;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for the Dispatcher class.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
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

    /**
     * Verifies that the configure call adds the serviceConfigurations to the dispatcher.
     */
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
     * Verifies that an exception is thrown when there is a configuration file missing.
     *
     * @expectedException \Spewia\Dispatcher\Exception\FileNotFoundException
     */
    public function testConfigureMissingFile()
    {
        $this->object->configure($this->configuration_directory);
    }

    /**
     * Verifies the default behaviour of the run() method.
     */
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
                'action' => 'show',
                'params' => array()
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

    /**
     * Verifies that when the route can't be matched, a 404 page is shown.
     */
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

    /**
     * Verifies that when the given controller can't be found, a 5xx error is thrown.
     */
    public function testRunControllerNotFound()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];
        $error_controller = \Mockery::mock('Spewia\Controller\ErrorController');
        $controller_not_found_exception = new \Spewia\Controller\Factory\Exception\UnknownClassException();

        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andReturn(
            array(
                'controller' => '\DummyController',
                'action' => 'show',
                'params' => array()
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

        $error_controller->shouldReceive('error5xxAction');
        //  ->with($controller_not_found_exception);

        $error_controller->shouldReceive('render')
            ->andReturn($response);

        $response->shouldReceive('send');

        $this->object->configure($this->configuration_directory)->run();
    }

    /**
     * Verifies that when the action can't be called, a 5xx error is thrown.
     */
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
                'action' => 'show',
                'params' => array()
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

        $this->assertFalse($controller->wasRenderCalled(),
            'The render method shouldn\'t have been called.');
    }

    /**
     * Verifies that when there is an exception thrown by the controller, a 5xx error is thrown.
     */
    public function testActionThrowsException()
    {
        $this->createRequiredConfigurationFiles();
        $basic_mocks = $this->prepareBasicMocks();

        $router = $basic_mocks['router'];
        $request = $basic_mocks['request'];
        $controller_factory = $basic_mocks['controller_factory'];
        $response = $basic_mocks['response'];

        $controller = \Mockery::mock('Spewia\Controller\ControllerInterface');
        $error_controller = \Mockery::mock('Spewia\Controller\ErrorController');
        $exception = new \Exception();

        $router->shouldReceive('parseRequest')
            ->with($request)
            ->andReturn(
            array(
                'controller' => '\DummyController',
                'action' => 'show',
                'params' => array()
            )
        );

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\DummyController'
        ))
            ->andReturn($controller);

        $controller->shouldReceive('showAction')
            ->once()
            ->withNoArgs()
            ->andThrow($exception);

        $controller->shouldReceive('render')
            ->never();

        $controller_factory->shouldReceive('build')
            ->with(array(
            'class' => '\Spewia\Controller\ErrorController'
        ))
            ->andReturn($error_controller);

        $error_controller->shouldReceive('error5xxAction')
            ->with($exception);

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

    /**
     * Builds the mocks which are always needed for the run method:
     *
     * * router
     * * request
     * * controller_factory
     * * response
     *
     * And adds all of them but the response to the dependency injection container.
     *
     * @return array
     */
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

/**
 * Dummy dispatcher which mocks the dependency injection container.
 */
class Dispatcher extends BaseDispatcher
{
    /**
     * Method which returns a mock of the container and adds a global "$mock".
     *
     * @global $mock
     * @param array $configuration
     * @return \Mockery\MockInterface|\Spewia\DependencyInjection\Container
     */
    protected function createDependencyInjectionContainer(array $configuration = array())
    {
        global $mock;
        $mock = \Mockery::mock('\Spewia\DependencyInjector\Container');
        return $mock;
    }
}

/**
 * Fake controller which shouldn't have it's render method called.
 */
class FakeController implements \Spewia\Controller\ControllerInterface
{
    protected $render_called = false;

    public function render()
    {
        $this->render_called = true;
    }

    /**
     * Returns if the render method of the controller was called.
     *
     * @return bool
     */
    public function wasRenderCalled()
    {
        return $this->render_called;
    }
}
