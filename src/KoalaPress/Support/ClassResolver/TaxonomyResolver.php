<?php

namespace KoalaPress\Support\ClassResolver;

class TaxonomyResolver extends ClassResolver
{
    /**
     * @var string The namespace to search for post type classes.
     */
    protected static string $namespace = 'Theme\App\Model\Taxonomy';

    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.taxonomies';

    /**
     * Get the namespace for post type classes.
     *
     * @return string
     */
    protected static function getNamespace(): string
    {
        return app()->getNamespace() . 'Model\Taxonomy';
    }
}
