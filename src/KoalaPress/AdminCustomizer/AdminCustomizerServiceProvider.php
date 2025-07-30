<?php

namespace KoalaPress\AdminCustomizer;

use Illuminate\Support\ServiceProvider;

class AdminCustomizerServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/admin-customizer.php',
            'admin-customizer'
        );

        $config = config('admin-customizer');

        /**
         * Hide the themes menu if configured.
         */
        if ($config['hide_themes']) {
            add_action('admin_menu', function () {
                remove_menu_page('themes.php', 'themes.php');
            }, 999);
        }

        /**
         * Add navigation menus to the admin menu if configured.
         */
        if ($config['add_nav_menus_to_admin_menu']) {
            add_action('admin_menu', function () {
                add_menu_page(
                    __('Navigation'),
                    __('Navigation'),
                    'edit_theme_options',
                    'nav-menus.php',
                    '',
                    'dashicons-menu',
                    60
                );
            }, 999);
        }

        /**
         * Add an elevated user role if configured.
         */
        if ($config['add_elevated_user']) {
            $editor = get_role('editor');
            $editor->add_cap('edit_theme_options');

            $editor_capabilities = $editor->capabilities;
            $additional_capabilities = array(
                'list_users' => true,
                'promote_users' => true,
                'remove_users' => true,
                'edit_users' => true,
                'create_users' => true,
                'delete_users' => true,
                'switch_themes' => true,
                'manage_options' => true,
                'manage_privacy_options' => true,
            );
            $elevated_editor_capabilities = array_merge($editor_capabilities, $additional_capabilities);

            add_role('elevated_editor', 'Super-Redakteur', $elevated_editor_capabilities);
            $elevated_editor = get_role('elevated_editor');
            $elevated_editor->add_cap('edit_theme_options');

            remove_role('contributor');
            remove_role('subscriber');

            add_filter(
                'editable_roles',
                function ($roles) {
                    if (!current_user_can('administrator')) {
                        unset($roles['administrator']);
                    }

                    return $roles;
                }
            );
        }

        if ($config['hide_default_posts']) {
            add_action('admin_menu', function () {
                remove_menu_page('edit.php');
            });
            add_action('admin_bar_menu', function ($wp_admin_bar) {
                $wp_admin_bar->remove_node('new-post');
            }, 999);
        }

        if($config['remove_authors']) {
            add_action('admin_init', function () {
                remove_post_type_support('page', 'author');
                remove_post_type_support('post', 'author');
            });
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->publishes([
            __DIR__ . '/../../../config/admin-customizer.php' => $this->app->configPath('admin-customizer.php'),
        ], 'config');
    }
}
