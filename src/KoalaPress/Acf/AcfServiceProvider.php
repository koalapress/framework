<?php

namespace KoalaPress\Acf;

use Illuminate\Support\ServiceProvider;
use KoalaPress\Acf\AcfWysiwygCustomizer;

class AcfServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/acf.php',
            'acf'
        );


    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        new AcfWysiwygCustomizer();

        $this->publishes([
              __DIR__ . '/../../../config/acf.php' => $this->app->configPath('acf.php'),
          ], 'config');
    }
}
