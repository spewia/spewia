<?php

namespace Spewia\Router;

use Symfony\Component\HttpFoundation\Request;
use Spewia\Router\Exception\RouteNotFoundException;

class Router implements RouterInterface
{
    protected $routing_configuration;

    /**
     * Loads the routes included in the configuration files
     *
		 * @param Array $configuration
     */
    public function __construct($configuration)
    {
        $this->routing_configuration = $configuration;
    }

    /**
     * Gets a Request and check if there is a configuration defined for it
     * @see Spewia\Router.RouterInterface::parseRequest()
     */
    public function parseRequest(Request $request)
    {
        //check if any of the entries in the patterns is the same that the uri passed in the Request
        $identifier = $this->getIdentifierByUri($request->getUri());

        if ($identifier === NULL) {
            throw new RouteNotFoundException();
        }
        return $this->routing_configuration[$identifier];
    }

    public function buildUri($identifier, array $params = array())
    {
        if (array_key_exists($identifier, $this->routing_configuration)) {
            $uri = $this->routing_configuration[$identifier]['pattern'];

            if (!empty($params)) {
                foreach($params AS $key => $value) {
                    $uri = str_replace('{'.$key.'}', $value, $uri);
                }
            }

            return $uri;
        }
        return NULL;
    }

    /**
     * Check if the $uri passed exists in the patterns in the configuration.
     *
     * @param string $uri
     * @return string identifier of the pattern that match the pattern
     */
    protected function getIdentifierByUri($uri)
    {
        //check if the pattern is inside the configuration
        foreach($this->routing_configuration AS $identifier => $configuration) {
            //if the pattern has parameters defined, change to convert it into regexp
            $pattern_regexp = '%' . preg_replace('%{\w+}%','\w+',$configuration['pattern']) . '%';

            if (preg_match($pattern_regexp, $uri)) {
                return $identifier;
            }
        }
        return NULL;
    }

}
