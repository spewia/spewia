<?php
/**
 * Dependency Injection Container basic file.
 */

return array(
    'request' => array(
        'class'   => '\Symfony\Component\HttpFoundation\Request',
        'factory' => array(
            'class'  => '\Symfony\Component\HttpFoundation\Request',
            'method' => 'createFromGlobals'
        )
    ),
    'factory.controller' => array(
        'class'                  => '\Spewia\Controller\Factory\ControllerFactory',
        'constructor_parameters' => array(
            array(
                'type' => 'service',
                'id'   => 'container'
            )
        )
    ),
    'factory.response' => array(
        'class' => '\Spewia\Response\Factory\ResponseFactory'
    ),
    'template'  => array(
        'class' => '\Spewia\Template\TwigTemplate'
    )
);