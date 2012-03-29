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

    public function setUp()
    {
        $this->object = new Dispatcher();
        $this->folder = vfsStream::setup('root');

        global $mock;
        $this->container = $mock;
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

        $this->container->shouldReceive('addServiceConfigurations')
            ->once()
            ->with(include vfsStream::url('root/dic_configuration.php'));

        $this->object->configure(vfsStream::url('root'));
    }

    /**
     * @expectedException \Spewia\Dispatcher\Exception\FileNotFoundException
     */
    public function testConfigureMissingFile()
    {
        $this->object->configure(vfsStream::url('root'));
    }

    public function testRun()
    {
        $this->markTestIncomplete();
    }

    public function testRunControllerNotFound()
    {
        $this->markTestIncomplete();
    }
}

class Dispatcher extends BaseDispatcher
{
    protected function createDependencyInjectionContainer()
    {
        global $mock;
        $mock = \Mockery::mock('\Spewia\DependencyInjector\Container');
        return $mock;
    }
}
