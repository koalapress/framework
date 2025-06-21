<?php

namespace KoalaPress\FlexibleContent;

use Illuminate\Support\ServiceProvider;
use KoalaPress\FlexibleContent\Acf\Locations\FlexibleContentLocation;
use KoalaPress\FlexibleContent\Finder\LayoutFinder;
use KoalaPress\Support\ClassResolver\PostTypeResolver;

class FlexibleContentServiceProvider extends ServiceProvider
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

        add_action('acf/init', function() {
            $this->registerAcfLocations();

            $fieldGroup = include __DIR__ . DIRECTORY_SEPARATOR . 'Acf' . DIRECTORY_SEPARATOR . 'Fields' . DIRECTORY_SEPARATOR . 'group_flexible-content.php';

            $fieldGroup['location'] = $this->getLocations();
            $fieldGroup['fields']['main']['layouts'] = $this->getLayouts();

            acf_add_local_field_group($fieldGroup);

            $this->addAcfeFlexibleLayout();
        });
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

            if ($model->useFlexibleContent) {
                return [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $model->getPostType(),
                    ]
                ];
            }
            return null;
        })
            ->filter()
            ->toArray();
    }

    /**
     * Get the layouts for the ACF field group.
     *
     * @return array
     */
    private function getLayouts(): array
    {
        return LayoutFinder::find();
    }

    /**
     * Register ACF location type for flexible content.
     *
     * @return void
     */
    private function registerAcfLocations(): void
    {
        if (!function_exists('acf_register_location_type')) {
            return;
        }

        acf_register_location_type(FlexibleContentLocation::class);
    }

    private function addAcfeFlexibleLayout(): void
    {
        /* TODO add_filter('acfe/flexible/render/template/key=field_flexible_content',
            function ($file, $field, $layout, $is_preview) {


                $data = [];
                foreach ($layout['sub_fields'] as $subField) {
                    $data[$subField['name']] = get_sub_field($subField['name']);
                }

                $layoutName = is_array($layout)
                    ? ($layout['name'] ?? $layout['acf_fc_layout'] ?? null)
                    : $layout;

                if (!$layoutName) {
                    echo '<div style="color:red;">[KoalaBlockPreview] Missing layout name.</div>';
                    return;
                }

                $view = 'module.' . $layoutName;

                if (!View::exists($view)) {
                    echo "<div style='color:gray;'>[KoalaBlockPreview] View not found: {$view}</div>";
                    return;
                }

                // Pass preview flag to templates
                $data['is_preview'] = $is_preview;

                echo View::make($view, $data)->render();
                return false;
            }, 10, PHP_INT_MAX); */
    }
}
