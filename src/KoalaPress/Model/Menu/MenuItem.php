<?php

namespace KoalaPress\Model\Menu;

use Corcel\Model\CustomLink;
use Corcel\Model\Meta\PostMeta;
use Corcel\Model\Page;
use Corcel\Model\Post;
use Corcel\Model\Taxonomy;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Arr;
use WP_Error;
use WP_Post;

class MenuItem extends \Corcel\Model\MenuItem
{
    /**
     * @var string
     */
    protected $postType = 'nav_menu_item';

    /**
     * @var array
     */
    protected $instanceRelations = [
        'post' => \KoalaPress\Model\PostType\Model::class,
        'page' => \KoalaPress\Model\PostType\Model::class,
        'custom' => CustomLink::class,
        'category' => Taxonomy::class,
    ];

    /**
     * MenuItem constructor.
     *
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return array|mixed|WP_Post|null
     */
    private function getWpPostClasses()
    {
        $post = \wp_setup_nav_menu_item(\get_post($this->meta->_menu_item_object_id));

        $items = [$post];

        \_wp_menu_item_classes_by_context($items);

        $post = reset($items);

        return $post;
    }

    /**
     * @return Post|Page|CustomLink|Taxonomy
     */
    public function parent(): Taxonomy|CustomLink|Page|Post|null
    {
        if ($className = $this->getClassName()) {
            return (new $className())->newQuery()
                ->find($this->meta->_menu_item_menu_item_parent);
        }

        return null;
    }

    /**
     * @return Post|Page|CustomLink|Taxonomy
     */
    public function instance(): Taxonomy|CustomLink|Page|Post|null
    {
        if ($className = $this->getClassName()) {
            return (new $className())->newQuery()
                ->find($this->meta->_menu_item_object_id);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getClassName(): string
    {
        return Arr::get(
            $this->instanceRelations,
            $this->meta->_menu_item_object
        );
    }

    /**
     * @return false|mixed|string|WP_Error
     */
    public function getUrlAttribute(): mixed
    {
        switch ($this->_menu_item_type) {
            case 'taxonomy':
                $tax = $this->instance()->toArray();

                return \get_term_link((int)$tax['term_taxonomy_id'], $tax['taxonomy']);
                break;

            case 'custom':
                return $this->_menu_item_url;
                break;

            case 'post_type_archive':
                return \get_post_type_archive_link($this->_menu_item_object);
                break;

            case 'post_type':
                return \get_permalink($this->instance()->ID);
                break;
        }
    }

    /**
     * @return int|mixed|string|WP_Error|null
     */
    public function getTitleAttribute(): mixed
    {
        if ($this->post_title) {
            return $this->post_title;
        }

        if (is_object($this->instance()) && $this->instance()->post_title) {
            return $this->instance()->post_title;
        }

        switch ($this->_menu_item_type) {
            case 'taxonomy':
                $tax = $this->instance()->toArray();

                return \get_term_field('name', (int)$tax['term_taxonomy_id'], $tax['taxonomy'], 'raw');
                break;

            case 'post_type_archive':
                $obj = get_post_type_object($this->_menu_item_object);

                return $obj->labels->name;
                break;
            default:
                return $this->instance()->post_title;
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentAttribute(): mixed
    {
        $post = $this->getWpPostClasses();

        return $post->current;
    }

    /**
     * @return mixed
     */
    public function getCurrentItemParentAttribute(): mixed
    {
        $context = $GLOBALS['sloth::plugin']->getContext();

        if (isset($context['post'])) {
            $instance = $this->instance();
            $id = is_object($instance) ? $instance->ID : $instance['ID'];

            if ($context['post']->parent_id === $id) {
                return true;
            }

            if ($this->_menu_item_type === 'post_type_archive' && $context['post']->postType === $this->_menu_item_object) {
                return true;
            }

            if (get_option('link_overview_' . $context['post']->postType)) {
                return (int)get_option('link_overview_' . $context['post']->postType) === $id;
            }
        }

        $post = $this->getWpPostClasses();

        return $post->current_item_parent;
    }

    /**
     * @return mixed
     */
    public function getCurrentItemAncestorAttribute(): mixed
    {
        $post = $this->getWpPostClasses();

        return $post->current_item_ancestor;
    }

    /**
     * @return bool
     */
    public function getInCurrentPathAttribute(): bool
    {
        return $this->getCurrentAttribute() || $this->getCurrentItemParentAttribute();
    }

    /**
     * @return string
     */
    public function getClassesAttribute(): string
    {
        $post = $this->getWpPostClasses();

        $classes = $post->classes;

        if ($post->current) {
            $classes[] = 'current';
            $classes[] = 'active';
        }

        if ($post->current_item_parent) {
            $classes[] = 'current_item_parent';
        }

        if ($post->current_item_ancestor) {
            $classes[] = 'current_item_ancestor';
        }

        return trim(implode(' ', array_filter($classes)));
    }

    /**
     * @return HasManyThrough
     */
    public function children(): HasManyThrough
    {
        return $this->hasManyThrough(
            MenuItem::class,
            PostMeta::class,
            'meta_value',
            'ID',
            'ID',
            'post_id'
        )->where('ID', '!=', $this->ID);
    }
}
