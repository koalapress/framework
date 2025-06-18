<?php

namespace KoalaPress\View\Engines;

use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\ViewFinderInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigEngine extends PhpEngine {
    /**
     * @var Environment
     */
    protected Environment $environment;

    /**
     * @var ViewFinderInterface
     */
    protected ViewFinderInterface $finder;

    /**
     * @var string
     */
    protected string $extension = '.twig';

    public function __construct( Environment $environment, ViewFinderInterface $finder ) {
        $this->environment = $environment;
        $this->finder      = $finder;
    }

    /**
     * Return the evaluated template.
     *
     * @param string $path The file name with its file extension.
     * @param array $data Template data (view data)
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function get( $path, array $data = [] ): string
    {

        foreach ( $this->finder->getPaths() as $realpath ) {
            $pattern = '~^' . realpath( $realpath ) . '~';
            if ( preg_match( $pattern, $path ) ) {
                $path = preg_replace( $pattern, '', $path );
                break;
            }
        }
        if (!str_ends_with($path, $this->extension)) {
            $path .= $this->extension;
        }

        return $this->environment->render( $path, $data );
    }
}
