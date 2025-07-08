<?php

namespace KoalaPress\PluginsManager;

class MuPluginsManager extends AbstractPluginsManagerInterface
{
    /**
     * @inheritDoc
     */
    protected function sourcePath(): string
    {
        return storage_path('plugins/mu-plugins');
    }

    /**
     * @inheritDoc
     */
    protected function targetPath(): string
    {
        return WPMU_PLUGIN_DIR;
    }

    /**
     * @inheritDoc
     */
    protected function composerType(): string
    {
        return 'wordpress-muplugin';
    }
}
