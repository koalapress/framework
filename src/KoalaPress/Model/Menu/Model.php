<?php

namespace KoalaPress\Model\Menu;

use Corcel\Model\Builder\TaxonomyBuilder;
use Corcel\Model\Menu;

class Model extends Menu
{
    /**
     * The location of the menu.
     *
     * @var string|null
     */

    public ?string $location = null;
    /**
     * The name of the menu.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * Use slug to query the menu.
     *
     * @return TaxonomyBuilder
     */
    public function newQuery(): TaxonomyBuilder
    {
        return parent::newQuery()->where('term_id', $this->getId());
    }

    /**
     * @return false|int|string
     */
    public function getLocationAttribute(): false|int|string|null
    {
        if (is_null($this->location)) {
            return $this->getLocation();
        }

        return $this->location;
    }


    /**
     * @return false|int|string
     */
    private function getLocation()
    {
        $locations = get_nav_menu_locations();

        return array_search($this->term_taxonomy_id, $locations);
    }

    /**
     * @return mixed
     */
    private function getId()
    {
        $locations = get_nav_menu_locations();

        $id = null;

        foreach ($locations as $location => $location_id) {
            if ($location === $this->location) {
                $id = $location_id;
                break;
            }
        }

        return $id;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(
            MenuItem::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        )->orderBy('menu_order');
    }

    /**
     * Recursively transform a menu item and its children.
     *
     * @param $item
     * @return array
     */
    protected function transformMenuItem($item)
    {
        return [
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
            'children' => $item->children
                ? $item->children->map(fn($child) => $this->transformMenuItem($child))->toArray()
                : [],
        ];
    }

    /**
     * @return array
     */
    public function nestify(): array
    {
        return $this->items()
            ->get()
            ->filter(fn($item) => $item->parent() === null)
            ->map(fn($item) => $this->transformMenuItem($item))
            ->toArray() ?? [];
    }
}
