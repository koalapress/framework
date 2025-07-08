<?php

namespace KoalaPress\PluginsManager;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PluginsManagerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * Merges the image sizes configuration from the specified file.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the image sizes configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/plugins-manager.php',
            'plugins-manager'
        );
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        add_action('init', function () {
            $plugins = new PluginsManager();
            $plugins->sync();

            $muPlugins = new MuPluginsManager();
            $muPlugins->sync();

            $activator = new PluginsActivator();
            $activator->activate($plugins->getInstalledPlugins());
        });
    }
}
