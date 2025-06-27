<?php

namespace KoalaPress\Support\ModelResolver;

use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\PostType\Model;

class PostTypeResolver
{
    /**
     * Resolve the model class for a given post type.
     *
     * @param string $postType The post type to resolve.
     * @return string The fully qualified class name of the model.
     */
    public static function resolve(string $postType): string
    {
        $postType = strtolower($postType);

        $postTypes = Cache::get('koalapress.post-types.matching', []);

        if (isset($postTypes[$postType])) {
            return $postTypes[$postType];
        }
        return Model::class;
    }
}
