<?php

namespace KoalaPress\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KoalaPress\FlexibleContent\FlexibleContentServiceProvider;
use KoalaPress\Model\PostType\PostTypeServiceProvider;
use KoalaPress\Model\Taxonomy\TaxonomyServiceProvider;
use KoalaPress\View\ViewServiceProvider;
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
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(FlexibleContentServiceProvider::class);
    }
}
