<?php

namespace Spewia\Template;

interface TemplateInterface
{
    /**
     * Assigns the given parameter to its key.
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value);

    /**
     * Sets the template file to render.
     * @param string $file
     */
    public function setTemplateFile($file);

    /**
     * Returns the rendered template.
     *
     * @return String
     */
    public function render();
}