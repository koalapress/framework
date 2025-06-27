<?php

namespace KoalaPress\View\Composers;

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
        $post = view()->shared('post');

        return [
            'post' => $post ?: null,
            $post ? strtolower(class_basename($post)) : 'model' => $post,
        ];
    }
}
