<?php

namespace Spewia\Controller;

/**
 * Controller Interface to be implemented by all the controllers.
 *
 */
interface ControllerInterface {

    /**
     * Render the data assigned to the Reponse and Template properties by the Controller.
     */
    public function render();

}
