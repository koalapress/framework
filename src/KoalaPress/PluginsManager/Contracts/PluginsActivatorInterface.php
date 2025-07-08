<?php

namespace KoalaPress\PluginsManager\Contracts;

interface PluginsActivatorInterface
{
    /**
     * Aktiviert Plugins anhand ihrer Slugs (Ordnernamen).
     *
     * @param array $slugs Slugs wie ['acf', 'yoast', ...]
     * @return void
     */
    public function activate(array $slugs): void;
}
