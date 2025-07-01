<?php

namespace KoalaPress\Support\ClassResolver;


use KoalaPress\Model\Taxonomy\Model;

class TaxonomyResolver extends ClassResolver
{
    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.taxonomies';

    /**
     * @var string The directory where taxonomy classes are located.
     */
    protected static string $dir = 'Model/Taxonomy';

    /**
     * @var string The base class that all post type classes should extend.
     */
    protected static string $subclassOf = Model::class;
}
