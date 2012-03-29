<?php

namespace Tests\Spewia\Dispatcher;

use Spewia\Dispatcher\Dispatcher;
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

    public function setUp()
    {
        $this->object = new Dispatcher();
        $this->folder = vfsStream::setup('root');

        $this->object = \Mockery::mock($this->object);

        $this->container = \Mockery::mock('\Spewia\DependencyInjector\Container');

        $this->object->shouldReceive('getDependencyInjectionContainer')
            ->andReturn($this->container);
    }

    public function testConfigure()
    {
        $file = vfsStream::newFile('dic_configuration.php');

        $this->folder->addChild($file);

        $configuration = <<<DIC_CONFIGURATION
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

        $file->setContent($configuration);

        $this->object->configure(vfsStream::url($this->folder));

        // TODO: Verify that the dependency injector got the new configuration.
    }

    public function testConfigureMissingFile()
    {}

    public function testRun()
    {}

    public function testRunControllerNotFound()
    {}
}
