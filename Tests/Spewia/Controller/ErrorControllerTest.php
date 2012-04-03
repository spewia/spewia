<?php

namespace Tests\Spewia\Controller;

use Spewia\Controller\ErrorController;
use Spewia\Exception\Exception;

class ErrorControllerTests extends \PHPUnit_Framework_TestCase
{
    protected $template;
    protected $container;
    protected $response;
    protected $controller;
    protected $exception;

    public function setUp()
    {
        $this->template = \Mockery::mock('Spewia\Template\TemplateInterface');

        $this->response = \Mockery::mock('Symfony\Component\HttpFoundation\Response');
        $factory = \Mockery::mock('Spewia\Factory\FactoryInterface');

        $this->container = \Mockery::mock('Spewia\DependencyInjection\ContainerInterface');

        $this->container
        ->shouldReceive('get')
        ->with('factory.response')
        ->andReturn($factory);

        $factory
        ->shouldReceive('build')
        ->andReturn($this->response);

        $this->container
        ->shouldReceive('get')
        ->with('template')
        ->andReturn($this->template);

        $this->exception = new Exception();

        $this->controller = new ErrorController($this->container);
    }

    public function testError404()
    {
        $this->template
        ->shouldReceive('render')
        ->once();

        $this->response
        ->shouldReceive('setContent')
        ->atLeast(1);

        $this->response
        ->shouldReceive('setStatusCode')
        ->with('404');

        $this->controller->error404Action($this->exception);
        $response = $this->controller->render();

        $this->assertSame($this->response, $response,
            'The response is not the same as the returned by the controller.'
        );
    }

    public function testError5xx()
    {
        $this->template
        ->shouldReceive('render')
        ->once();

        $this->response
        ->shouldReceive('setContent')
        ->atLeast(1);

        $this->response
        ->shouldReceive('setStatusCode')
        ->with('500');


        $this->controller->error5xxAction($this->exception);
        $response = $this->controller->render();

        $this->assertSame($this->response, $response,
            'The response is not the same as the returned by the controller.'
        );
    }
}