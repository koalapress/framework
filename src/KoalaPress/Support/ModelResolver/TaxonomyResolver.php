<?php

namespace KoalaPress\Support\ModelResolver;

use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\Taxonomy\Model;

class TaxonomyResolver
{
    /**
     * Resolve the model class for a given post type.
     *
     * @param string $taxonomy The post type to resolve.
     * @return string The fully qualified class name of the model.
     */
    public static function resolve(string $taxonomy): string
    {
        $taxonomy = strtolower($taxonomy);

        $taxonomies = Cache::get('koalapress.taxonomy.matching', []);

        if (isset($taxonomies[$taxonomy])) {
            return $taxonomies[$taxonomy];
        }

        return Model::class;
    }
}
