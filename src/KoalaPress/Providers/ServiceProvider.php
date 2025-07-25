<?php

namespace KoalaPress\Providers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KoalaPress\FlexibleContent\FlexibleContentServiceProvider;
use KoalaPress\Model\Menu\MenuServiceProvider;
use KoalaPress\Model\PostType\PostTypeServiceProvider;
use KoalaPress\Model\Taxonomy\TaxonomyServiceProvider;
use KoalaPress\PluginsManager\PluginsManagerServiceProvider;
use KoalaPress\Support\ModelResolver\ModelResolver;
use KoalaPress\View\ViewServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Roots\Acorn\Sage\SageServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(SageServiceProvider::class);
        $this->app->register(PostTypeServiceProvider::class);
        $this->app->register(TaxonomyServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(FlexibleContentServiceProvider::class);
        $this->app->register(PluginsManagerServiceProvider::class);
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
        add_action('wp', function () {
            $post = ModelResolver::findFromQuery();

            view()->share([
                'post' => $post,
                $post ? strtolower(class_basename($post)) : 'model' => $post,
            ]);
        });
    }
}
