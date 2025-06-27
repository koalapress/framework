<?php

use Illuminate\Support\Str;
use KoalaPress\Image\Image;

function module($name, $args = [])
{
    $moduleClass = app()->getNamespace() . '\Module\\' . Str::studly($name);

    if (class_exists($moduleClass)) {
        $module = new $moduleClass($args);
        return $module->render();
    }
    return '<div class="module-error">' .
        __('Module not found') .
        '</div>';
}


/** * Generate an image source set for responsive images.
 *
 * @return array The HTML source set attribute.
 */
function source_set(): array
{
    return app(Image::class)->getSourceset(...func_get_args());
}
