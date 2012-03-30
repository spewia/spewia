<?php

namespace Spewia\Factory;
/**
 * Interface to be implemented by all the possible factories.
 */
interface FactoryInterface
{
    /**
     * Builds an element as established by the array options. Each factory has to document all it's possible options.
     *
     * @param array $options Options given to the factory to determine what to build.
     *
     * @return mixed
     */
    public function build(array $options = array());
}
