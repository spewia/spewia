<?php

namespace Spewia\Component;

interface ComponentInterface
{
    /**
     * Renders to component.
     * @return string The rendered component call.
     */
    public function render();
}