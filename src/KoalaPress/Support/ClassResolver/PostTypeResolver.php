<?php

namespace KoalaPress\Support\ClassResolver;

class PostTypeResolver extends ClassResolver
{
    /**
     * @var string The namespace to search for post type classes.
     */
    protected static string $namespace = 'Theme\App\Model\PostType';

    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.post-types';
}
