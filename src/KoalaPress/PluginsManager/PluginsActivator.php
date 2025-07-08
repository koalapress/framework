<?php

namespace KoalaPress\PluginsManager;

use KoalaPress\PluginsManager\Contracts\PluginsActivatorInterface;

class PluginsActivator implements PluginsActivatorInterface
{
    /**
     * Activates the specified plugins by their slugs.
     *
     * This method checks if each plugin is already active, and if not,
     * it activates the plugin and logs the activation.
     *
     * @param array $slugs An array of plugin slugs to activate.
     */
    public function activate(array $slugs): void
    {
        $slugs = collect($slugs);
        $config = config('plugins-manager');
        $blacklist = $config['blacklist'] ?? [];

        collect(get_plugins())->each(function ($plugin, $pluginFile) use ($slugs, $blacklist) {
            $slug = dirname($pluginFile);

            if ($slugs->contains($slug) && !is_plugin_active($pluginFile) && !in_array($slug, $blacklist)) {
                activate_plugin($pluginFile);
                error_log("🔌 Plugin '$pluginFile' aktiviert.");
            }
        });
    }
}
