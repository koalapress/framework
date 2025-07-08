<?php

namespace KoalaPress\PluginsManager;

class PluginsManager extends AbstractPluginsManagerInterface
{
    /**
     * @inheritDoc
     */
    protected function sourcePath(): string
    {
        return storage_path('plugins/plugins');
    }

    /**
     * @inheritDoc
     */
    protected function targetPath(): string
    {
        return WP_PLUGIN_DIR;
    }

    /**
     * @inheritDoc
     */
    protected function composerType(): string
    {
        return 'wordpress-plugin';
    }
}
