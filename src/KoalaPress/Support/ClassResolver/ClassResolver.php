<?php

namespace KoalaPress\Support\ClassResolver;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use KoalaPress\Model\PostType\Model;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

abstract class ClassResolver
{
    /**
     * @var string The directory where post type classes are located.
     */
    protected static string $dir = 'Models/PostType';

    /**
     * The cache key for storing resolved classes.
     *
     * @var string
     */
    protected static string $cacheKey = 'koalapress.class-resolver.classes';


    /**
     * @var string The base class that all post type classes should extend.
     */
    protected static string $subclassOf = Model::class;

    /**
     * @throws ReflectionException
     */
    public static function resolve(): Collection
    {
        return app()->isLocal() ?
            Cache::rememberForever(static::$cacheKey, fn() => self::collectModels()) :
            self::collectModels();
    }

    /**
     * @return Collection
     * @throws ReflectionException
     */
    private static function collectModels(): Collection
    {
        if (!is_dir($path = app()->path(static::$dir))) {
            return collect();
        }

        $namespace = app()->getNamespace();

        $models = [];

        foreach ((new Finder)->in($path)->files() as $model) {
            $model = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($model->getPathname(), app()->path() . DIRECTORY_SEPARATOR)
                );
            if (
                is_subclass_of($model, static::$subclassOf) &&
                !(new ReflectionClass($model))->isAbstract()
            ) {
                $models[] = $model;
            }
        }
        return collect($models);
    }
}
