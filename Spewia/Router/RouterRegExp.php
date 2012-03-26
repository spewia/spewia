<?php

namespace Spewia\Router;

use Symfony\Component\HttpFoundation\Request;
use Spewia\Router\Exception\RouteNotFoundException;

/**
 * Class to make the routing with the Spewia syntax, that is more extensible than the normal syntax
 * defined in the Spewia\Router\Router class.
 *
 * The syntax has been defined as an own extension of regular expression in order to allows the user more
 * options to the user, allowing him to make less routes to make the same calls.
 *
 * The syntax will follow the next rules:
 *  * You don't need to set the begin and end delimiter for regular expressions.
 *  They will be automatically added by the code. If you add them, it will cause problems because the duplicity.
 *
 *  * To define optional parameters, they will be encapsulated into {} parameters.
 *  Ex: "route{_page}" will match "route" and "route_page"
 *
 *  * To defined variables, you have to use the following syntax: "<variable_name:regular_expression>" where
 *  ** "<>" are the delimiters of the variable
 *  ** "variable_name" will be the name of the variable to transform into.
 *  ** "regular_expression" will be the regular expression to match to get the value of the variable.
 *
 *  The characters have been selected because the RFC-3986 has defined that they are not supposed to be inside an Uri syntax
 *  http://www.ietf.org/rfc/rfc3986.txt
 *
 * @author Aitor Suso <patxi1980@gmail.com>
 *
 */
class RouterRegExp implements RouterInterface
{
    /**
     * Configuration file with all the routing values.
     * @var array
     */
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
     * (non-PHPdoc)
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

    /**
     * (non-PHPdoc)
     * @see Spewia\Router.RouterInterface::buildUri()
     */
    public function buildUri($identifier, array $params = array())
    {
        if (array_key_exists($identifier, $this->routing_configuration)) {
          $uri = $this->routing_configuration[$identifier]['pattern'];

          foreach ($params AS $key => $value) {
            $uri = preg_replace('/\(\?P<'.$key.'>[^()]+\)', $value, $uri);
          }

          //delete all the optionals fields in the uri that has not been replaced.
          $uri = preg_replace('/\(\?\:(.+)\)/','$1',$uri);

          //delete the possible initial and final delimiters in the reg_exp
          $uri = preg_replace('/\^(.+)\$/','$1',$uri);



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

          if (preg_match('%'.$configuration['pattern'].'%', $uri)) {
            return $identifier;
          }
        }
        return NULL;
    }

}
