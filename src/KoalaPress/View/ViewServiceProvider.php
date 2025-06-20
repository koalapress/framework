<?php

namespace KoalaPress\View;

use Illuminate\Support\ServiceProvider;
use KoalaPress\View\Engines\TwigEngine;
use KoalaPress\View\Extensions\TwigExtension;
use KoalaPress\View\Loader\TwigLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('twig', function ($app) {
            $loader = new TwigLoader($this->app->resourcePath('views'));
            return new Environment($loader, [
                'cache' => storage_path('framework/views/twig'),
                'auto_reload' => !$app->environment('production'),
                'debug' => $app->environment('development'),
                'autoescape' => false,
            ]);
        });
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app['view']->addExtension('twig', 'twig', function () {
            return new TwigEngine($this->app['twig'], $this->app['view']->getFinder());
        });

        // register twig extensions
        $this->app['twig']->addExtension(new DebugExtension());
        $this->app['twig']->addExtension(new TwigExtension($this->app));

    }
}
