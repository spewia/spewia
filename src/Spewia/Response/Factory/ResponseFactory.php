<?php

namespace Spewia\Response\Factory;

use Spewia\Factory\FactoryInterface;
use Spewia\Response\Response;
/**
 * Class which builds response objects.
 *
 * @author Roger Llopart Pla <lumbendil@gmail.com>
 */
class ResponseFactory implements FactoryInterface
{
    /**
     * Builds a response object. This has no options.
     *
     * @param array $options Unused in this factory.
     *
     * @return \Spewia\Response\Response
     */
    public function build(array $options = array())
    {
        return new Response();
    }
}
