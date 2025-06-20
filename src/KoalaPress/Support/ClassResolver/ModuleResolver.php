<?php

namespace KoalaPress\Support\ClassResolver;

class ModuleResolver extends ClassResolver
{
    /**
     * @var string The namespace to search for post type classes.
     */
    protected static string $namespace = 'Theme\App\Module';

    /**
     * @var string The cache key for storing resolved post type classes.
     */
    protected static string $cacheKey = 'koalapress.modules';
}
