<?php

namespace Spewia\Template;

use Spewia\Template\TemplateInterface;

/**
 * Class wich wraps the Twig system.
 */
class TwigTemplate implements TemplateInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Twig_LoaderInterface
     */
    protected $loader;

    /**
     * @var \Twig_Template
     */
    protected $template;

    /**
     * The assigned parameters.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Builds the TwigTemplate object.
     *
     * @param array|string $paths path or paths to give to the template file loader.
     */
    public function __construct($paths = array())
    {
        $this->loader = new \Twig_Loader_Filesystem($paths);

        $this->twig = new \Twig_Environment($this->loader);
    }
    /**
     * Assigns the given parameter to its key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Sets the template file to render.
     *
     * @param string $file
     */
    public function setTemplateFile($file)
    {
        $this->template = $this->twig->loadTemplate($file);
    }

    /**
     * Returns the rendered template.
     *
     * @return String
     */
    public function render()
    {
        return $this->template->render($this->parameters);
    }

    /**
     * Adds a folder to the loader.
     *
     * @param $folder
     */
    public function addFolder($folder)
    {
        $this->loader->addPath($folder);
    }
}