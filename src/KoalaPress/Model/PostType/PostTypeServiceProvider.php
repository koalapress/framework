<?php

namespace KoalaPress\Model\PostType;

use Composer\Autoload\ClassLoader;
use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use KoalaPress\Support\ClassResolver\PostTypeResolver;
use PostTypes\PostType;

class PostTypeServiceProvider extends ServiceProvider
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
        $this->registerPostTypes();
    }

    /**
     * Register Post Types
     *
     * @return void
     * @throws Exception
     */
    protected function registerPostTypes(): void
    {
        try {
            #$classes = PostTypeResolver::resolve();
            $classes =
                collect(ClassFinder::getClassesInNamespace('Theme\\App\\Model\\PostType\\'));

            $classes->each(function ($class) {
                $model = new $class();
                dd($class);

                if (post_type_exists($model->getPostType())) {
                    unregister_post_type($model->getPostType());

                    do_action('unregistered_post_type', $model->getPostType());
                }

                $names = array_merge($model->names, ['name' => $model->getPostType()]);
                $options = $model->options;

                if (isset($model->icon)) {
                    $options = array_merge(
                        $model->options,
                        ['menu_icon' => 'dashicons-' . preg_replace('/^dashicons-/', '', $model->icon)]
                    );
                }
                $labels = $model->labels;

                $postType = new PostType($model->getPostType());
                $postType->names($names);
                $postType->options($options);
                $postType->labels($labels);

                $postType->columns()->hide($model->admin_columns_hidden);

                foreach ($model->admin_columns as $k => $v) {
                    if (is_numeric($k)) {
                        $model->admin_columns[$v] = ucfirst($v);
                        unset($model->admin_columns[$k]);
                    }
                }

                $postType->columns()->add($model->admin_columns);

                $order['title'] = 1;
                $idx = in_array('title', $model->admin_columns_hidden) ? 1 : 2;
                $order = [];
                $sortable = [];

                foreach ($model->admin_columns as $k => $v) {
                    $postType->columns()->populate(
                        $k,
                        function ($column, $post_id) use ($class, $k) {
                            $method = 'get' . ucfirst($k) . 'Column';
                            $result = $class::find($post_id);
                            echo $result->{$method}();
                        }
                    );

                    $sortable[$k] = $k;
                    $order[$k] = $idx;
                    ++$idx;
                }

                $order['date'] = $idx + 1;

                $postType->columns()->order($order);

                $postType->columns()->sortable($sortable);

                if (in_array('title', $model->admin_columns_hidden)) {
                    $keys = array_keys($model->admin_columns);
                    $first_column = reset($keys);
                    add_filter(
                        'list_table_primary_column',
                        function ($default, $screen) use ($postType, $first_column) {
                            if ('edit-' . $postType->name === $screen) {
                                $default = $first_column;
                            }

                            return $default;
                        },
                        10,
                        2
                    );
                }

                $postType->register();
                $postType->flush(true);
            });
        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }
}
