<?php

use Illuminate\Support\Str;

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


