<?php

namespace KoalaPress\Support\ClassResolver;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

abstract class ClassResolver
{
    /**
     * The namespace to search for classes.
     *
     * @var string
     */
    protected static string $namespace = 'App';

    /**
     * The cache key for storing resolved classes.
     *
     * @var string
     */
    protected static string $cacheKey = 'koalapress.class-resolver.classes';

    /**
     * Set the namespace to search for classes.
     *
     * @return Collection
     */
    public static function resolve(): Collection
    {
        $finder = fn() => collect(ClassFinder::getClassesInNamespace(static::$namespace));

        return Cache::rememberForever(static::$cacheKey, $finder);
    }
}
