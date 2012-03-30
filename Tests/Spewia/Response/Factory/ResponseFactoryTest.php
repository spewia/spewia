<?php

namespace Tests\Spewia\Response\Factory;

use Spewia\Response\Factory\ResponseFactory;

/**
 * Tests for the ResponseFactory.
 */
class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseFactory
     */
    protected $object;

    public function setUp()
    {
        $this->object = new ResponseFactory;
    }

    /**
     * Tests the standard behaviour of the factory.
     */
    public function testBuild()
    {
        $response = $this->object->build();

        $this->assertInstanceOf( '\Spewia\Response\Response' ,$response,
            'The returned object is an instance of Response.');

        $this->assertEquals('', $response->getContent(),
            'The content should be empty.');

        $this->assertEquals(200, $response->getStatusCode(),
            'The response status should be 200.');
    }
}
