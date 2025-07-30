<?php

namespace KoalaPress\AcfExtended;

use Illuminate\Support\ServiceProvider;
use KoalaPress\FlexibleContent\Acf\Locations\FlexibleContentLocation;
use KoalaPress\FlexibleContent\Finder\LayoutFinder;
use KoalaPress\Support\ClassResolver\PostTypeResolver;

use function KoalaPress\FlexibleContent\add_action;

class AcfExtendedServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {

        $resolver = function() {
            return app()->basePath('app/ACF/FieldGroups');
        };

        add_filter(
            'acfe/settings/php_save',
            $resolver,
            10);

        add_filter(
            'acfe/settings/php_load',
            $resolver,
            10);
    }
}
