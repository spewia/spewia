<?php

namespace Tests\Spewia\RouterSpewia;

use Spewia\Router\RouterSpewia;

use Symfony\Component\HttpFoundation\Request;

class RouterSpewiaTest extends \PHPUnit_Framework_TestCase
{
    protected $router;

    public function setUp()
    {
        //Create the routing configuration array
        $routing_configuration = array(
                    'page' => array(
                        'pattern'				=> '/page',
        								'controller'    => 'page',
                        'action'				=> 'default',
                    ),
                    'list' => array(
                        'pattern'				=> '/list{_<page:\d+>}',
                        'controller'    => 'list',
                        'action'				=> 'default',
                        'defaults'			=> array(
                            'page' => 1,
                        )
                    ),
        );

        $this->router = new RouterSpewia($routing_configuration);
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
        ->andReturn('/page');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller',$routerParams,
                    'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action',$routerParams,
										'There is no "action" defined in the $routerParams array.'
        );

        $this->assertEquals(
                    'page',$routerParams['controller'],
                		'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
                    'default',$routerParams['action'],
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
        ->andReturn('/page2');

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
        ->andReturn('/list_2');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller',$routerParams,
                            'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action',$routerParams,
        										'There is no "action" defined in the $routerParams array.'
        );

        $this->assertArrayHasKey('params',$routerParams,
        										'There is no "params" defined in the $routerParams array.'
        );

        $this->assertArrayHasKey('page',$routerParams['params'],
        										'There is no "page" defined in the $routerParams["params"] array.'
        );

        $this->assertEquals(
                            'list',$routerParams['controller'],
                        		'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
                            'default',$routerParams['action'],
                        		'The "action" returned is not the desired one.'
        );

        $this->assertEquals(
                            '2',$routerParams['params']['page'],
                        		'The "page" returned is not the desired one.'
        );
    }

    public function testRouterWithDefaultParams()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $request
        ->shouldReceive('getUri')
        ->andReturn('/list');

        $routerParams = $this->router->parseRequest($request);

        $this->assertTrue(is_array($routerParams), 'The routerParams is not an array');

        $this->assertArrayHasKey('controller',$routerParams,
                            'There is no "controller" defined in the $routerParams array.'
        );
        $this->assertArrayHasKey('action',$routerParams,
        										'There is no "action" defined in the $routerParams array.'
        );

        $this->assertArrayHasKey('params',$routerParams,
        										'There is no "params" defined in the $routerParams array.'
        );

        $this->assertArrayHasKey('page',$routerParams['params'],
        										'There is no "page" defined in the $routerParams["params"] array.'
        );

        $this->assertEquals(
                            'list',$routerParams['controller'],
                        		'The "controller" returned is not the desired one.'
        );

        $this->assertEquals(
                            'default',$routerParams['action'],
                        		'The "action" returned is not the desired one.'
        );

        $this->assertEquals(
                            '1',$routerParams['params']['page'],
                        		'The "page" returned is not the desired one.'
        );

    }

    /**
     * Test the buildUri method with no parameters
     */
    public function testBuildUriWithNoParameters()
    {
        $identifier = 'page';

        $uri = $this->router->buildUri($identifier);

        $this->assertNotNull(
            $uri,
        		'There is not route for this identifier.'
        );

        $this->assertEquals(
            '/page', $uri,
            'The uri returned is not the expected one.'
        );
    }

    /**
     * Test the buildUri method with parameters
     */
    public function testBuildUriWithParameters()
    {
        $identifier = 'list';
        $params = array('page' => 2);

        $uri = $this->router->buildUri($identifier, $params);

        $this->assertNotNull(
            $uri,
    				'There is not route for this identifier.'
        );

        $this->assertEquals(
            '/list_2', $uri,
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