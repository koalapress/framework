<?php

namespace KoalaPress\Context;

use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\PostType\Model;

class Context
{
    private Model $model;
    /**
     * Context constructor.
     */
    public function __construct()
    {
        $queried = get_queried_object();
        $matching = Cache::get('koalapress.post-types.matching', []);

        $modelClass = $matching[$queried->post_type] ?? Model::class;

        $this->model = $modelClass::find($queried->ID);
    }

    public function getAll(): array
    {
        return [
            'post' => $this->model,
            $this->model->getPostType() => $this->model,
        ];
    }
}
