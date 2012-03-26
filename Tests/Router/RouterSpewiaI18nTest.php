<?php

namespace Tests\Spewia\RouterSpewia;

use Spewia\Router\RouterSpewiaI18n;

use Symfony\Component\HttpFoundation\Request;

class RouterSpewiaI18nTest extends \PHPUnit_Framework_TestCase
{
    protected $router;

    public function setUp()
    {
        //Create the routing configuration array
        $routing_configuration = array(
                    'page' => array(
                        'pattern'				=> array(
                            'en_US'     => '/page',
                            'es_ES'			=> '/pagina',
                        ),
        								'controller'    => 'page',
                        'action'				=> 'default',
                    ),
                    'home' => array(
                        'pattern'			 => array(
                            'en_US'    => '/home',
                        ),
        								'controller'    => 'page',
                        'action'				=> 'home',
                    ),
                    'list' => array(
        								'pattern'				=> array(
                            'en_US'     => '/list{_<page:\d+>',
                            'es_ES'			=> '/listado_{<page:\d+>',
                        ),
                        'controller'    => 'list',
                        'action'				=> 'default',
                        'defaults'			=> array(
                            'page' => 1,
                        )
                    ),
                    'about' => array(
        								'pattern'				=> '/about',
        								'controller'    => 'page',
                        'action'				=> 'about',
                    ),
        );

        $this->router = new RouterSpewiaI18n($routing_configuration, 'en_US');
    }

    /**
     * Test the i18n routing in spanish
     * It set Spanish in the session in order to get it from the controller.
     * Enter description here ...
     */
    public function testControllerActionRoutingI18n()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/pagina');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

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
     * Test the i18n routing with the default language.
     * There is no language set, so it gets the default one.
     */
    public function testControllerActionRoutingI18nWithDefaultLangage()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
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
     * Send a Uri that is correct in one language, but the session is set in another one.
     *
     * @expectedException Spewia\Router\Exception\RouteI18nIncorrectLanguageException
     */
    public function testControllerActionRoutingI18nWithWrongLanguage()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/page');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

        $routerParams = $this->router->parseRequest($request);
    }

    /**
     * Send a Uri that has the same pattern for all the languages.
     */
    public function testControllerActionRoutingI18nWithNoLanguage()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/about');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

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
                                    'about',$routerParams['action'],
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
        ->andReturn('/pagina2');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

        $routerParams = $this->router->parseRequest($request);

    }

    /**
     * Test the router with parameters
     */
    public function testControllerActionRoutingI18nWithParams()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/listado_2');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

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

    /**
    * Test the router with parameters, that have been set as defaults.
    */
    public function testControllerActionRoutingI18nWithDefaultParams()
    {
        //Create the request
        $request = \Mockery::mock('Symfony\Component\HttpFoundation\Request');

        $request
        ->shouldReceive('getUri')
        ->once()
        ->andReturn('/listado');

        $request
        ->shouldReceive('getLocale')
        ->once()
        ->andReturn('/es_ES');

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
     * Build the Uri by its identifier without parameters.
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
                    '/pagina', $uri,
                    'The uri returned is not the expected one.'
        );
    }

    /**
     * Build the Uri by its identifier with parameters.
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
                    '/listado_2', $uri,
                    'The uri returned is not the expected one.'
        );
    }

    /**
     * Try to build the Uri for an identifier that doesn't exists.
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

    /**
     * Build the Uri with an specific language setted in the call.
     */
    public function testBuildUriWithSpecificLanguage()
    {
        $identifier = 'page';
        $language = 'en_US';

        $uri = $this->router->buildUri($identifier, array(), $language);

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
     * Build the Uri when the language is the default one
     */
    public function testBuildUriWithDefaultLanguage()
    {
        $identifier = 'home';

        $uri = $this->router->buildUri($identifier);

        $this->assertNotNull(
        $uri,
                        		'There is not route for this identifier.'
        );

        $this->assertEquals(
                            '/home', $uri,
                            'The uri returned is not the expected one.'
        );
    }

    /**
     * Build the Uri when the configuration file has no language in it.
     */
    public function testBuildUriWithNoLanguages()
    {
        $identifier = 'about';

        $uri = $this->router->buildUri($identifier);

        $this->assertNotNull(
        $uri,
                        		'There is not route for this identifier.'
        );

        $this->assertEquals(
                            '/about', $uri,
                            'The uri returned is not the expected one.'
        );
    }

}