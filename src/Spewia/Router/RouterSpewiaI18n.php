<?php

namespace Spewia\Router;

use Symfony\Component\HttpFoundation\Request;
use Spewia\Router\Exception\RouteNotFoundException;

/**
 * Class to make the routing with the Spewia syntax and i18n language routing definition.
 * It is based in the RouterSpewia, but it allows to add i18n language support in order to define the same route
 * in different languages.
 *
 * In the 'pattern' inside the configuration, you can place an array with the different language.
 * The keys of this array will be the language.
 *
 * The languages could be defined in 2 digits or in 5 digits formats.
 *
 * To read about Spewia syntax for routing
 * @see Spewia\Router\RouterSpewia
 *
 * @author Aitor Suso <patxi1980@gmail.com>
 *
 */
class RouterSpewiaI18n extends RouterSpewia implements RouterInterface
{
    /**
     * Configuration file with all the routing values.
     * @var array
     */
    protected $routing_configuration;

    /**
		 * Language to set as default when there is not a pattern defined for the current language.
		 * @var string
     */
    protected $default_language = 'en_US';

    /**
     * Loads the routes included in the configuration files
     *
		 * @param Array $configuration
     */
    public function __construct($configuration, $default_language = NULL, $language = NULL)
    {
        $this->routing_configuration = $configuration;

        $this->setDefaultLanguage($default_language);
    }

    public function setDefaultLanguage($language)
    {
        if ($this->verifyLanguage($language)) {
          $this->default_language = $language;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Spewia\Router.RouterInterface::parseRequest()
     * @throws Spewia\Router\Exception\RouteI18nIncorrectLanguageException
     */
    public function parseRequest(Request $request)
    {
        //check if any of the entries in the patterns is the same that the uri passed in the Request
        $identifier = $this->getIdentifierByUri($request->getPathInfo(), $request->getLocale());

        if ($identifier === NULL) {
          throw new RouteNotFoundException();
        }

        $params = $this->getParamsFromRequestUri($identifier, $request->getPathInfo(), $request->getLocale());

        $this->routing_configuration[$identifier]['params'] = $params;

        return $this->routing_configuration[$identifier];
    }

    /**
     * (non-PHPdoc)
     * @see Spewia\Router.RouterInterface::buildUri()
     */
    public function buildUri($identifier, array $params = array(), $language = NULL)
    {
        if (array_key_exists($identifier, $this->routing_configuration)) {
          $uri = $this->routing_configuration[$identifier]['pattern'];

          //check if the pattern configuration is an array with the multiple languages defined.
          if (is_array($uri)) {
              //replace the $uri with the language defined, or with the default language if this is not defined.
              if (array_key_exists($language, $uri)) {
                  $uri = $uri[$language];
              } elseif (array_key_exists($this->default_language, $uri)) {
                  $uri = $uri[$this->default_language];
              } else {
                  //if the pattern is not in the language, return NULL
                  return NULL;
              }
          }

          //replace the params <page:\d+>
          foreach ($params AS $key => $value) {
            //$uri = str_replace($key, $value, $uri);
            $uri = preg_replace('/<(['.$key.']+):[^>]+>/', $value, $uri);
          }

          //delete all the optionals fields in the uri that has not been replaced.
          $uri = preg_replace('/{[^<]+<[^>]+>[^}]*}/', '', $uri);

          //delete all the remaining optinal fields markers "{}". The one that have been replaced by its value.
          $uri = preg_replace('/{([^}]+)}/','$1',$uri);

          return $uri;
        }
        return NULL;
    }

    /**
    * Check if the $uri passed exists in the patterns in the configuration.
    *
    *
    * @param string $uri
    * @return string identifier of the pattern that match the pattern
    */
    protected function getIdentifierByUri($uri, $locale = NULL)
    {
      if (!$locale) {
          $locale = $this->default_language;
      }

      //check if the pattern is inside the configuration
      foreach($this->routing_configuration AS $identifier => $configuration) {

        //if the pattern value is an array, check the language
        if (is_array($configuration['pattern'])) {

            $pattern = $this->getPatternFromConfiguration($configuration, $locale);

            if ($pattern) {
                //else check the value
                if (preg_match('%'.$pattern.'%', $uri)) {
                  return $identifier;
                }
            }
        } else {
            //else check the value
            $pattern = $this->convertSpewiaIntoRegExp($configuration['pattern']);

            if (preg_match('%'.$pattern.'%', $uri)) {
              return $identifier;
            }
        }
      }
      return NULL;
    }

    /**
     * Check if the language passed is in the two or five letters format.

     * @param string $language
     * @return boolean True if is in the correct format.
     */
    protected function verifyLanguage($language)
    {
        return preg_match('/^\w{2}(?:_\w{2})?$/',$language);
    }

    /**
    * From the requested uri, get all the parameters to pass them to desired action.
    *
    * @param String $identifier
    * @param String $request_uri Url to parse.
    * @return Array Parameters
    */
    protected function getParamsFromRequestUri($identifier, $request_uri, $locale = NULL)
    {
        if (!$locale) {
            $locale = $this->default_language;
        }

        $pattern = $this->getPatternFromConfiguration($this->routing_configuration[$identifier], $locale);

        if (!$pattern) {
            return null;
        }

        //created the pattern to get the varibles from the Spewia pattern.
        $regexp_pattern = '!'.$this->convertSpewiaIntoRegExp($pattern).'!';

        //get all the variables passed in the request
        preg_match_all($regexp_pattern, $request_uri, $params);

        // clean the params array returned by the preg_match
        foreach ($params AS $key => $value) {
            if (is_numeric($key)) {
                unset($params[$key]);
            } else {
                $params[$key] = $value[0];
                if (!$value[0]) {
                    unset($params[$key]);
                }
            }
        }

        //if the defaults are defined, merge them for the non-set variables.
        if (array_key_exists('defaults', $this->routing_configuration[$identifier])) {
            $params = array_merge($this->routing_configuration[$identifier]['defaults'], $params);
        }

        return $params;
    }

    protected function getPatternFromConfiguration($configuration, $locale = NULL)
    {
        if (!$locale) {
          $locale = $this->default_language;
        }
        $pattern = NULL;

        if (is_array($configuration['pattern'])) {
            //check if the language is any of the keys defined in the pattern language array.
            if (array_key_exists($locale, $configuration['pattern'])) {
              $pattern = $this->convertSpewiaIntoRegExp($configuration['pattern'][$locale]);
            } else {
              if (array_key_exists($this->default_language, $configuration['pattern'])) {
                $pattern = $this->convertSpewiaIntoRegExp($configuration['pattern'][$this->default_language]);
              }
            }
        } else {
            $pattern = $configuration['pattern'];
        }

        return $pattern;
    }
}