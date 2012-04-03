<?php

namespace Tests\Spewia\Router;

use Spewia\Router\Router;

use Symfony\Component\HttpFoundation\Request;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $router;

    public function setUp()
    {
        //Create the routing configuration array
        $routing_configuration = array(
            'test'      => array(
                'pattern'                => '/test/test',
                'controller'             => 'test',
                'action'                 => 'test',
            ),
            'test_page' => array(
                'pattern'                => '/test/test/{page}',
                'controller'             => 'test',
                'action'                 => 'test',
            ),
        );

        $this->router = new Router($routing_configuration);
    }

    /**
     * Test the Router in order to check if it returns excepted values
     */
    public function testControllerActionRouting()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $request
            ->shouldReceive('getPathInfo')
            ->once()
            ->andReturn('/test/test');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller', $routerParams,
            'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action', $routerParams,
            'There is no "action" defined in the $routerParams array.'
        );

        $this->assertEquals(
            'test', $routerParams['controller'],
            'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
            'test', $routerParams['action'],
            'The "action" returned is not the desired one.'
        );

    }

    /**
     * Test the router when the identifier passed is not defined in the router.
     *
     * @expectedException Spewia\Router\Exception\RouteNotFoundException
     */
    public function testRouteNotFound()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $request
            ->shouldReceive('getPathInfo')
            ->once()
            ->andReturn('/test2/test2');

        $routerParams = $this->router->parseRequest($request);

    }

    /**
     * Test the router when the identifier passed is defined with params in it.
     */
    public function testRouteWithParams()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $request
            ->shouldReceive('getPathInfo')
            ->once()
            ->andReturn('/test/test/2');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller', $routerParams,
            'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action', $routerParams,
            'There is no "action" defined in the $routerParams array.'
        );

        $this->assertEquals(
            'test', $routerParams['controller'],
            'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
            'test', $routerParams['action'],
            'The "action" returned is not the desired one.'
        );
    }

    /**
     * Test the buildUri method with no parameters
     */
    public function testBuildUriWithNoParameters()
    {
        $identifier = 'test';

        $uri = $this->router->buildUri($identifier);

        $this->assertNotNull(
            $uri,
            'There is not route for this identifier.'
        );

        $this->assertEquals(
            '/test/test', $uri,
            'The uri returned is not the expected one.'
        );
    }

    /**
     * Test the buildUri method with parameters
     */
    public function testBuildUriWithParameters()
    {
        $identifier = 'test_page';
        $params     = array('page' => 2);

        $uri = $this->router->buildUri($identifier, $params);

        $this->assertNotNull(
            $uri,
            'There is not route for this identifier.'
        );

        $this->assertEquals(
            '/test/test/2', $uri,
            'The uri returned is not the expected one.'
        );
    }

    /**
     * Test the buildUri method when there identifier is not defined.
     */
    public function testBuildUriWithNotDefinedIdentifier()
    {
        $identifier = 'test2';

        $uri = $this->router->buildUri($identifier);

        $this->assertNull(
            $uri,
            'There is a route for an identifier that is not defined.'
        );

    }

}