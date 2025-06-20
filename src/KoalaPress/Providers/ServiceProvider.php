<?php

namespace KoalaPress\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KoalaPress\DynamicContent\DynamicContentServiceProvider;
use KoalaPress\Model\PostType\PostTypeServiceProvider;
use KoalaPress\Model\Taxonomy\TaxonomyServiceProvider;
use KoalaPress\Template\TemplateServiceProvider;
use KoalaPress\View\ViewServiceProvider;

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
        $this->app->register(TemplateServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(DynamicContentServiceProvider::class);
    }
}
