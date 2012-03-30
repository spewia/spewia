<?php

namespace Spewia\Controller;

/**
 * Controller Interface to be implemented by all the controllers.
 */
interface ControllerInterface
{
    /**
     * Render the Response and the Template created in the controller.
     */
    public function render();
}
