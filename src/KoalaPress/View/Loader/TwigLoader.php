<?php

namespace KoalaPress\View\Loader;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class TwigLoader extends FilesystemLoader
{
    /**
     *
     * @param string $name The template name, which can be in dot notation.
     * @param bool $throw Whether to throw an exception if the template is not found.
     * @throws LoaderError
     */
    public function findTemplate(string $name, bool $throw = true): ?string
    {
        // Convert dot notation to slashes and add .twig if missing
        if (str_contains($name, '.') && !str_ends_with($name, '.twig')) {
            $name = str_replace('.', '/', $name) . '.twig';
        }
        return parent::findTemplate($name, $throw);
    }
}
