<?php

namespace Spewia\Controller;

interface ControllerInterface
{
    /**
     * Render the Response and the Template created in the controller.
     */
    public function render();
}
