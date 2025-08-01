<?php

namespace KoalaPress\ResponsiveImage;

use Illuminate\Support\ServiceProvider;

use Tracy\Debugger;

use function Roots\add_filters;

class ResponsiveImageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /* TODO:
         $this->loadViewsFrom(__DIR__ . '/../resources/views', 'responsive-image');
            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/responsive-image'),
            ]);
        */
        add_filters(
            [
                'wp_generate_attachment_metadata',
                'wp_update_attachment_metadata',
                'wp_generate_attachment_metadata_async',
                'attachment_updated',
            ],
            function () {
                Debugger::barDump(
                    'Regenerating responsive images',
                    'info'
                );
            },
            10
        );
    }
}
