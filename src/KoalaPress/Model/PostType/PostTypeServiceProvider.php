<?php

namespace KoalaPress\Model\PostType;

use Exception;
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
            $classes = PostTypeResolver::resolve();
            $postTypeMatching = [];

            $classes->each(function ($class) use (&$postTypeMatching) {
                $model = new $class();

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

                $postType->columns()->hide($model->adminColumnsHidden);

                foreach ($model->adminColumns as $k => $v) {
                    if (is_numeric($k)) {
                        $model->adminColumns[$v] = ucfirst($v);
                        unset($model->adminColumns[$k]);
                    }
                }

                $postType->columns()->add($model->adminColumns);

                $order['title'] = 1;
                $idx = in_array('title', $model->adminColumnsHidden) ? 1 : 2;
                $order = [];
                $sortable = [];

                foreach ($model->adminColumns as $k => $v) {
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

                if (in_array('title', $model->adminColumnsHidden)) {
                    $keys = array_keys($model->adminColumns);
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
                $postType->flush();

                $postTypeMatching[$model->getPostType()] = $class;
            });
            Cache::rememberForever('koalapress.post-types.matching', function () use ($postTypeMatching) {
                return $postTypeMatching;
            });
        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }
}
