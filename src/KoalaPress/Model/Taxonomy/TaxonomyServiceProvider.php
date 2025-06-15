<?php

namespace KoalaPress\Model\Taxonomy;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use PostTypes\Taxonomy;

class TaxonomyServiceProvider extends ServiceProvider
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
        $this->registerTaxonomies();
    }

    /**
     * Register Taxonomies
     *
     * @return void
     * @throws Exception
     */
    protected function registerTaxonomies(): void
    {
        try {
            $classes = $this->resolveTaxonomies();

            foreach ($classes as $class) {
                $taxonomy = new $class();

                $taxonomy->options['hierarchical'] = $taxonomy->hierarchical;

                if ($taxonomy->unique) {
                    $taxonomy->options['hierarchical'] = false;
                    $taxonomy->options['parent_item'] = null;
                    $taxonomy->options['parent_item_colon'] = null;
                }

                $names = array_merge($taxonomy->names, ['name' => $taxonomy->getTaxonomy()]);
                $options = $taxonomy->options;
                $labels = $taxonomy->labels;

                $tax = new Taxonomy($names, $options, $labels);

                foreach ($taxonomy->postTypes as $postType) {
                    $tax->posttype($postType);
                }

                foreach ($taxonomy->admin_columns as $k => $v) {
                    if (is_numeric($k)) {
                        $taxonomy->admin_columns[$v] = ucfirst($v);
                        unset($taxonomy->admin_columns[$k]);
                    }
                }

                $tax->columns()->add($taxonomy->admin_columns);

                $order['title'] = 1;
                $idx = in_array('title', $taxonomy->admin_columns_hidden) ? 1 : 2;
                $order = [];
                $sortable = [];

                $tax->columns()->hide($taxonomy->admin_columns_hidden);

                foreach ($taxonomy->admin_columns as $k => $v) {
                    $tax->columns()->populate(
                        $k,
                        function ($content, $column, $term_id) use ($class, $k) {
                            $method = 'get' . ucfirst($k) . 'Column';
                            $result = $class::find($term_id);
                            echo $result->{$method}();
                        }
                    );

                    $sortable[$k] = $k;
                    $order[$k] = $idx;
                    ++$idx;
                }

                $tax->columns()->order($order);

                $tax->columns()->sortable($sortable);

                if (in_array('name', $taxonomy->admin_columns_hidden)) {
                    $keys = array_keys($taxonomy->admin_columns);
                    $first_column = reset($keys);
                    add_filter(
                        'list_table_primary_column',
                        function ($default, $screen) use ($tax, $first_column) {
                            if ('edit-' . $tax->name === $screen) {
                                $default = $first_column;
                            }

                            return $default;
                        },
                        10,
                        2
                    );
                }

                $tax->register();

                if ($taxonomy->unique) {
                    add_action('admin_menu', function () use ($taxonomy) {
                        $me = $taxonomy;

                        foreach ($taxonomy->postTypes as $post_type) {
                            remove_meta_box('tagsdiv-' . $taxonomy->getTaxonomy(), $post_type, null);
                        }

                        $post_types = $taxonomy->postTypes;
                        add_action(
                            'add_meta_boxes',
                            function () use ($me, $post_types) {
                                add_meta_box(
                                    'koalapress-taxonomy-' . $me->getTaxonomy(),
                                    $me->names['singular'],
                                    function ($wp_post) use ($me, $post_types) {
                                        $tax = \KoalaPress\Model\PostType\Model::find($wp_post->ID)->taxonomies()->where('taxonomy', '=', $me->getTaxonomy())->first();

                                        $args = [
                                            'taxonomy' => $me->getTaxonomy(),
                                            'hide_empty' => 0,
                                            'name' => 'tax_input[' . $me->getTaxonomy() . '][0]',
                                            'value_field' => 'slug',
                                            'selected' => $tax ? $tax->slug : null,
                                        ];
                                        wp_dropdown_categories($args);
                                    },
                                    $post_types,
                                    'side'
                                );
                            },
                            10,
                            2
                        );
                    });
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Resolve Post Types
     *
     * @return array
     * @throws Exception
     */
    protected function resolveTaxonomies(): array
    {
        $finder = fn() => ClassFinder::getClassesInNamespace('Theme\App\Model\Taxonomy');

        return App::environment('development')
            ? $finder()
            : Cache::rememberForever('taxonomies', $finder);
    }

}
