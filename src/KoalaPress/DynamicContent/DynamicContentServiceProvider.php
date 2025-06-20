<?php

namespace KoalaPress\DynamicContent;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use KoalaPress\DynamicContent\Acf\Locations\DynamicContentLocation;
use KoalaPress\Support\ClassResolver\ModuleResolver;
use KoalaPress\Support\ClassResolver\PostTypeResolver;
use KoalaPress\Support\Helper\NamingHelper;
use ReflectionClass;

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
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        $this->registerAcfLocations();

        $fieldGroup = include __DIR__ . DIRECTORY_SEPARATOR . 'Acf' . DIRECTORY_SEPARATOR . 'Fields' . DIRECTORY_SEPARATOR . 'group_dynamic-content.php';

        $fieldGroup['location'] = $this->getLocations();
        $fieldGroup['fields']['main']['layouts'] = $this->getLayouts();

        acf_add_local_field_group($fieldGroup);

        $this->addAcfeFlexibleLayout();
    }

    /**
     * Get the locations for the ACF field group.
     *
     * @return array
     */
    private function getLocations(): array
    {
        $postTypes = PostTypeResolver::resolve();

        return $postTypes->map(function ($class) {
            $model = new $class();

            if ($model->useDynamicContent) {
                return [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $model->getPostType(),
                    ]
                ];
            }
            return null;
        })->filter()->toArray();
    }

    private function getLayouts(): array
    {
        $modules = ModuleResolver::resolve();

        return $modules
            ->mapWithKeys(function ($module) {
                $key = Str::snake(NamingHelper::getShortName($module));
                return [
                    $key => [
                        'key' => 'layout_' . $key,
                        'name' => $key,
                        'label' => $module::getLabel(),
                        'display' => 'block',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_' . $key,
                                'type' => 'clone',
                                'required' => 1,
                                'clone' => array(
                                    $module::getFieldGroup(),
                                ),
                                'acfe_seamless_style' => 0,
                                'acfe_clone_modal' => 0,
                                'acfe_clone_modal_close' => 0,
                                'acfe_clone_modal_button' => '',
                                'acfe_clone_modal_size' => 'large',
                            ),
                        ),
                        'min' => '',
                        'max' => '',
                        'acfe_flexible_render_template' => false,
                        'acfe_flexible_render_style' => false,
                        'acfe_flexible_render_script' => false,
                        'acfe_flexible_thumbnail' => false,
                        'acfe_flexible_settings' => false,
                        'acfe_flexible_settings_size' => 'medium',
                        'acfe_flexible_modal_edit_size' => false,
                        'acfe_flexible_category' => false,
                    ],
                ];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Register ACF location type for dynamic content.
     *
     * @return void
     */
    private function registerAcfLocations(): void
    {
        if (!function_exists('acf_register_location_type')) {
            return;
        }

        acf_register_location_type(DynamicContentLocation::class);
    }

    private function addAcfeFlexibleLayout(): void
    {
        add_filter('acfe/flexible/render/template/key=field_dynamic_content', function ($file, $field, $layout, $is_preview) {
            dump($field);
            dump($layout);
            dump(get_sub_field($field['key']));
            echo 'ACFE Flexible Render Template: ' . $layout . '<br>';
            return false;
        }, 10, 4);
    }
}
