<?php

namespace KoalaPress\Support\ModelResolver;

class ModelResolver
{

    /**
     * Resolve the model class from the given query object.
     *
     * @param mixed $query
     * @return string|null
     */
    public static function resolveFromQuery($query): ?string
    {
        return match (get_class($query)) {
            'WP_Term' => TaxonomyResolver::resolve($query->taxonomy),
            default => PostTypeResolver::resolve($query->post_type),
        };
    }

    /**
     * Find the model from the current query.
     *
     * This method retrieves the queried object and resolves the model class
     * based on the post type or taxonomy. It then finds the model instance
     * using the primary key of the queried object.
     *
     * @return array
     */
    public static function findFromQuery()
    {
        if(is_admin()) {
            return [];
        }
        $queried = get_queried_object();

        if (is_null($queried)) {
            return [];
        }

        $modelClass = self::resolveFromQuery($queried);


        $key = app($modelClass)->getKeyName();

        $model = $modelClass::find($queried->{$key});

        return $model;
    }
}
