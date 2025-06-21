<?php

namespace KoalaPress\Model\Menu;

use Exception;
use Illuminate\Support\ServiceProvider;
use KoalaPress\Support\ClassResolver\MenuResolver;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    /**
     * Boot the service provider.
     *
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        $this->registerMenus();
    }

    /**
     * Register Post Types
     *
     * @return void
     * @throws Exception
     */
    protected function registerMenus(): void
    {
        try {
            $classes = MenuResolver::resolve();
            $classes->each(function ($class) {
                $model = new $class();

                if (has_nav_menu($model->location)) {
                    unregister_nav_menu($model->location);
                    do_action('unregistered_nav_menu', $model->location);
                }

                register_nav_menu($model->location, $model->name);
            });
        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }
}
