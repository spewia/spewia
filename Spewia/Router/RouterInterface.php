<?php

namespace Spewia\Router;

use Symfony\Component\HttpFoundation\Request;
use Spewia\Router\Exception\RouteNotFoundException;

/**
 * Router interface to be implemented by all the routers.
 */
interface RouterInterface
{
    /**
     * Builds the Router with the given configuration.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration);

    /**
     * Parses the given request to match it to a route.
     *
     * @param Request $request
     *
     * @return Array Contains the 'controller', 'action', and 'params' keywords.
     * @throws RouteNotFoundException
     */
    public function parseRequest(Request $request);

    /**
     * Builds an URI given an ID and an optional parameters array.
     *
     * @param $identifier
     * @param array $params
     *
     * @return string The given URI.
     */
    public function buildUri($identifier, array $params = array());
}
