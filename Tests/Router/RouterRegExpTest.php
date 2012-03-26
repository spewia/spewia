<?php

namespace Tests\Spewia\RouterRegExp;

use Spewia\Router\RouterRegExp;

use Symfony\Component\HttpFoundation\Request;

class RouterRegExpTest extends \PHPUnit_Framework_TestCase
{
    protected $router;

    public function setUp()
    {
        //Create the routing configuration array
        $routing_configuration = array(
                    'test' => array(
                        'pattern'				=> '/test{_<page:\d+>}',
                        'controller'    => 'test',
                        'action'				=> 'test',
                        'defaults'			=> array(
                            'page' => 1,
                        )
                    ),
        );

        $this->router = new RouterRegExp($routing_configuration);
    }

    /**
     * Test the Router in order to check if it returns excepted values
     */
    public function testControllerActionRouting()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/test');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller',$routerParams,
                    'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action',$routerParams,
										'There is no "action" defined in the $routerParams array.'
        );

        $this->assertEquals(
                    'test',$routerParams['controller'],
                		'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
                    'test',$routerParams['action'],
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
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/test2');

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
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/test_2');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller',$routerParams,
                            'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action',$routerParams,
        										'There is no "action" defined in the $routerParams array.'
        );

        $this->assertEquals(
                            'test',$routerParams['controller'],
                        		'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
                            'test',$routerParams['action'],
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
            '/test', $uri,
            'The uri returned is not the expected one.'
        );
    }

    /**
     * Test the buildUri method with parameters
     */
    public function testBuildUriWithParameters()
    {
        $identifier = 'test';
        $params = array('page' => 2);

        $uri = $this->router->buildUri($identifier, $params);

        $this->assertNotNull(
            $uri,
    				'There is not route for this identifier.'
        );

        $this->assertEquals(
            '/test_2', $uri,
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