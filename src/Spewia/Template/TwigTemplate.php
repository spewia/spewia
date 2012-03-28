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

    public function __construct()
    {
        $this->loader = new \Twig_Loader_Filesystem(array());

        $this->twig = new \Twig_Environment($this->loader);
    }
    /**
     * Assigns the given parameter to its key.
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Sets the template file to render.
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

    public function addFolder($folder)
    {
        $this->loader->addPath($folder);
    }
}