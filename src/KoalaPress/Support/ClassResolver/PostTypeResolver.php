<?php

namespace KoalaPress\Support\ClassResolver;

use KoalaPress\Model\PostType\Model;

class PostTypeResolver extends ClassResolver
{
    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.post-types';

    /**
     * @var string The directory where post type classes are located.
     */
    protected static string $dir = 'Model/PostType';

    /**
     * @var string The base class that all post type classes should extend.
     */
    protected static string $subclassOf = Model::class;

}
