<?php

namespace Spewia\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * Router interface to be implemented by all the routers.
 */
interface RouterInterface
{
    /**
     * Parses the given request to match it to a route.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Array Contains the 'controller', 'action', and 'params' keywords.
     * @throws Spewia\Router\Exception\RouteNotFoundException
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
