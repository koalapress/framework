<?php

namespace KoalaPress\View\Composers;

use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\PostType\Model;
use KoalaPress\Support\ModelResolver\PostTypeResolver;
use Roots\Acorn\View\Composer;

class GlobalComposer extends Composer
{
    /**
     * The list of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = ['*'];

    /**
     *
     *
     * @return array
     */
    public function with(): array
    {
        $queried = get_queried_object();

        if (!$queried || !isset($queried->post_type) || !isset($queried->ID)) {
            return [];
        }

        $modelClass = PostTypeResolver::resolve($queried->post_type);

        $model = $modelClass::find($queried->ID);

        return [
            'post' => $model,
            $model->getPostType() => $model,
        ];
    }
}
