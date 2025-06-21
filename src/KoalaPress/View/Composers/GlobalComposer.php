<?php

namespace KoalaPress\View\Composers;

use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\PostType\Model;
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
        $matching = Cache::get('koalapress.post-types.matching', []);

        $modelClass = $matching[$queried->post_type] ?? Model::class;

        $model = $modelClass::find($queried->ID);

        return [
            'post' => $model,
            $model->getPostType() => $model,
        ];
    }
}
