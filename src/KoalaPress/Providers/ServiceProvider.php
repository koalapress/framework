<?php

namespace KoalaPress\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KoalaPress\Model\PostType\PostTypeServiceProvider;
use KoalaPress\Model\Taxonomy\TaxonomyServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(PostTypeServiceProvider::class);
        $this->app->register(TaxonomyServiceProvider::class);
    }
}
