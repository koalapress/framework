<?php

namespace KoalaPress\Image;

use Illuminate\Support\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the image sizes configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/image-sizes.php',
            'image-sizes'
        );
    }

    /**
     * Register the image sizes for the application.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../config/image-sizes.php' => $this->app->configPath('image-sizes.php'),
        ], 'config');

        $this->registerImageSizes();

        $this->app->singleton('image', function () {
            return new Image();
        });
    }

    /**
     * Register the image sizes based on the configuration.
     *
     * @return void
     */
    private function registerImageSizes(): void
    {
        $imageRatios = config('image-sizes.ratios', []);

        $calculatedImageSizes = [];

        if ($imageRatios != null && is_array($imageRatios)) {
            foreach ($imageRatios as $key => $imageSize) {
                $imageWidths = config('image-sizes.widths', []);


                foreach ($imageWidths as $imageWidth) {
                    if ($imageWidth > $imageSize['maxWidth']) {
                        continue;
                    }

                    $calculatedImageSizes[$key . '_' . $imageWidth] = [
                        'width' => $imageWidth,
                        'height' => isset($imageSize['width']) ? (int)round($imageWidth / $imageSize['width'] * $imageSize['height']) : null
                    ];
                }
            }
        }

        $calculatedImageSizes = array_merge($calculatedImageSizes, config('image-sizes.sizes', []));

        foreach ($calculatedImageSizes as $key => $imageSize) {
            add_image_size(
                $key,
                $imageSize['width'],
                $imageSize['height'] ?? null,
                $imageSize['crop'] ?? true
            );
        }
    }
}
