<?php

namespace KoalaPress\PluginsManager\Contracts;

interface PluginsManagerInterface
{
    /**
     * Synchronizes the plugins with the current theme's bundled plugins.
     *
     * This method reads the `composer.lock` file to determine which plugins
     * are required, copies them from the theme's bundled plugins directory
     * to the WordPress plugins directory, and marks them as managed by the theme.
     */
    public function sync(): void;
}
