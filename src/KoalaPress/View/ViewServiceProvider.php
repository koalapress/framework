<?php

namespace KoalaPress\View;

use KoalaPress\View\Composers\GlobalComposer;
use KoalaPress\View\Engines\TwigEngine;
use KoalaPress\View\Extensions\TwigExtension;
use KoalaPress\View\Loader\TwigLoader;
use Roots\Acorn\View\FileViewFinder;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Roots\Acorn\View\ViewServiceProvider as BaseViewServiceProvider;

class ViewServiceProvider extends BaseViewServiceProvider
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
        $this->registerViewFinder();
        $this->registerComposers();
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

    /**
     * Register View Finder
     *
     * @return void
     */
    public function registerViewFinder(): void
    {
        $this->app->bind('view.finder', function ($app) {
            $finder = new FileViewFinder($app['files'], array_unique($app['config']['view.paths']),
                ['twig', 'blade.php', 'php']);

            foreach ($app['config']['view.namespaces'] as $namespace => $hints) {
                $hints = array_merge(
                    array_map(fn($path) => "{$path}/vendor/{$namespace}", $finder->getPaths()),
                    (array)$hints
                );

                $finder->addNamespace($namespace, $hints);
            }

            return $finder;
        });

        $this->app->alias('view.finder', FileViewFinder::class);
    }

    /**
     * Register view composers.
     *
     * @return void
     */
    public function registerComposers(): void
    {
        $this->view()->composer(GlobalComposer::views(), GlobalComposer::class);
    }
}
