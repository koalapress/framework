<?php

namespace KoalaPress\DynamicContent;

use Illuminate\Support\ServiceProvider;
use KoalaPress\Support\ClassResolver\PostTypeResolver;

class DynamicContentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        // Register any bindings or singletons here if needed
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        /*if(!function_exists('acf_add_local_field_group')) {
            return;
        }*/

        #$fieldGroup = include __DIR__ . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR . 'group_dynamic-content.php';

        #$postTypes = PostTypeResolver::resolve();

        /*$postTypes->each(function ($class) use ($fieldGroup) {
            $model = new $class();
            dump($model->useDynamicContent);
        });*/
    }
}
