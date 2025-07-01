<?php

namespace KoalaPress\Support\ClassResolver;

use KoalaPress\Model\Menu\Model;

class MenuResolver extends ClassResolver
{

    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.menus';

    /**
     * @var string The directory where menu classes are located.
     */
    protected static string $dir = 'Model/Menu';

    /**
     * @var string The base class that all post type classes should extend.
     */
    protected static string $subclassOf = Model::class;
}
