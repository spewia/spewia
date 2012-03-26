<?php
namespace Tests\Spewia\DependencyInjection;

use Spewia\DependencyInjection\Container;

/**
 * Test class for Container.
 * Generated by PHPUnit on 2012-03-23 at 11:39:33.
 *
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const   FOO_CLASS       = '\Tests\Spewia\DependencyInjection\Foo',
            BAR_CLASS       = '\Tests\Spewia\DependencyInjection\Bar',
            FOOBAR_CLASS    = '\Tests\Spewia\DependencyInjection\FooBar';
    /**
     * @var Container
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Container(array(
            'foo' => array(
                'class' => self::FOO_CLASS
            ),
            'bar' => array(
                'class' => self::BAR_CLASS,
                'constructor_parameters' => array(
                    array(
                        'type'  => 'service',
                        'id'    => 'foo'
                    ),
                    array(
                        'type'  => 'constant',
                        'value' => 'string'
                    )
                )
            ),
            'foobar' => array(
                'class' => self::FOOBAR_CLASS,
                'constructor_parameters' => array(
                    array(
                        'type'  => 'service',
                        'id'    => 'bar2'
                    )
                )
            ),
            'bar2' => array(
                'class' => self::BAR_CLASS,
                'constructor_parameters' => array(
                    array(
                        'type'  => 'service',
                        'id'    => 'foobar'
                    ),
                    array(
                        'type'  => 'constant',
                        'value' => 'string'
                    )
                )
            ),
            'foobar2' => array(
                'class' => self::FOOBAR_CLASS,
                'constructor_parameters' => array(
                    array(
                        'type'  => 'service',
                        'id'    => 'bar'
                    )
                ),
                'configuration_calls' => array(
                    'setFoo'    => array(
                        array(
                            'type'  => 'service',
                            'id'    => 'foobar2'
                        )
                    )
                )
            ),
            'foobar3' => array(
                'class' => self::FOOBAR_CLASS,
                'constructor_parameters' => array(
                    array(
                        'type'  => 'service',
                        'id'    => 'bar'
                    )
                ),
                'configuration_calls' => array(
                    'setFoo' => array(
                        array(
                            'type'  => 'service',
                            'id'    => 'foo2'
                        )
                    )
                )
            ),
            'foo2' => array(
                'class' => self::FOO_CLASS,
                'configuration_calls' => array(
                    'setFoo' => array(
                        array(
                            'type'  => 'service',
                            'id'    => 'foo'
                        )
                    )
                )
            )
        ));
    }

    public function testGetWithNoDependencies()
    {
        $return = $this->object->get('foo');

        $this->assertInstanceOf(
            self::FOO_CLASS, $return,
            'The key "foo" expected an instance of type "Foo".');
    }

    public function testMultipleGetCalls()
    {
        $first = $this->object->get('foo');
        $second = $this->object->get('foo');

        $this->assertSame($first, $second,
            'Two calls on the same key should return the same object.');
    }

    public function testGetWithDependencies()
    {
        $return = $this->object->get('bar');

        $this->assertInstanceOf(self::BAR_CLASS, $return,
            'The key "bar" expected an instance of type "Bar".');

        $foo = $this->object->get('foo');

        $this->assertSame($foo, $return->getFoo(),
            'The "foo" key and the embedded "foo" element should be the same.');

        $this->assertEquals('string', $return->getString(),
            'The "string", element to be embedded should have the value "string".');
    }

    /**
     * @expectedException \Spewia\DependencyInjection\Exception\CircularDependencyException
     */
    public function testGetWithCircularDependency()
    {
        $this->object->get('foobar');
    }

    /**
     * @expectedException \Spewia\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testGetNonExistingService()
    {
        $this->object->get('unexisting_service');
    }

    public function testGetWithSetterDependencies()
    {
        $return = $this->object->get('foobar2');

        $this->assertSame($return, $return->getFoo(),
            'The two objects must be the same.');
    }

    public function testGetWithSetterDependenciesWichHaveSetterDependencies()
    {
        $return = $this->object->get('foobar3');

        $inner_dependecy = $return->getFoo()->getFoo();

        $this->assertInstanceOf(self::FOO_CLASS, $inner_dependecy,
            'The Foo object should contain a foo object itself.');

        $this->assertSame($this->object->get('foo'), $inner_dependecy,
            ' The inner dependency should be the same than the object fetched from calling $container->get("foo").');
    }
}

class Foo
{
    protected $foo;

    public function setFoo(Foo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}

class Bar
{
    protected $foo;
    protected $string;

    public function __construct(Foo $foo, $string)
    {
        $this->foo = $foo;
        $this->string = $string;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getString()
    {
        return $this->string;
    }
}

class FooBar extends Foo
{
    protected $bar;
    protected $foo;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function setFoo(Foo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}