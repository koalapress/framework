<?php

namespace KoalaPress\View\Extensions;

use Illuminate\Contracts\Foundation\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension
{
    /**
     * @var Application
     */
    protected Application $container;

    public function __construct(Application $container)
    {
        $this->container = $container;
    }

    /**
     * Define the extension name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'koala';
    }

    /**
     * Register a list of tests available into Twig templates.
     *
     * @return array|TwigTest[]
     */
    public function getTests(): array
    {
        return [
            new TwigTest('string', function ($value) {
                return is_string($value);
            }),
        ];
    }

    /**
     * Register a global "fn" which can be used
     * to call any WordPress or core PHP functions.
     *
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'fn' => $this,
        ];
    }

    /**
     * Allow developers to call core php and WordPress functions
     * using the `fn` namespace inside their templates.
     * Linked to the global call only...
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array($name, $arguments);
    }


    /**
     * Register a list of filters available into Twig templates.
     *
     * @return array|TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('tel', function ($phone) {
                return 'tel:' . preg_replace("/[^0-9\+]/", "", $phone);
            }),
            new TwigFilter('sanitize',
                function ($string) {
                    return sanitize_title($string);
                }),
            new TwigFilter('dump', function ($value) {
                if (function_exists('dump')) {
                    dump($value);
                } else {
                    var_dump($value);
                }
                return '';
            }),
        ];
    }

    /**
     * Register a list of functions available into Twig templates.
     *
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            /*
             * WordPress theme functions.
             */
            new TwigFunction('wp_head', 'wp_head'),
            new TwigFunction('wp_footer', 'wp_footer'),
            new TwigFunction('body_class', function ($class = '') {
                return body_class($class);
            }),
            new TwigFunction('post_class', function ($class = '', $id = null) {
                return post_class($class, $id);
            }),
            /*
             * WordPress formatting functions.
             */
            new TwigFunction('wpautop', function ($text, $br = true) {
                return wpautop($text, $br);
            }),
            new TwigFunction('wp_trim_words', function ($text, $num_words = 55, $more = null) {
                return wp_trim_words($text, $num_words, $more);
            }),
            new TwigFunction('get_field', function ($field_name, $post = null) {
                return get_field($field_name, $post);
            }),
            /*
             * Use this to call any core, WordPress or user defined functions.
             */
            new TwigFunction('function', function ($functionName) {
                $args = func_get_args();
                // By default, the function name should always be the first argument.
                // This remove it from the arguments list.
                array_shift($args);

                if (is_string($functionName)) {
                    $functionName = trim($functionName);
                }

                return call_user_func_array($functionName, $args);
            }),
            /*
             * Retrieve any meta data from post, comment, user, ...
             */
            new TwigFunction('meta', function ($key, $id = null, $context = 'post', $single = true) {
                return meta($key, $id, $context, $single);
            }),
            /*
             * Gettext functions.
             */
            new TwigFunction('translate', function ($text, $domain = 'default') {
                return translate($text, $domain);
            }),
            new TwigFunction('__', function ($text, $domain = 'default') {
                return __($text, $domain);
            }),
            new TwigFunction('_e', function ($text, $domain = 'default') {
                return _e($text, $domain);
            }),
            new TwigFunction('_n', function ($single, $plural, $number, $domain = 'default') {
                return _n($single, $plural, $number, $domain);
            }),
            new TwigFunction('_x', function ($text, $context, $domain = 'default') {
                return _x($text, $context, $domain);
            }),
            new TwigFunction('_ex', function ($text, $context, $domain = 'default') {
                return _ex($text, $context, $domain);
            }),
            new TwigFunction('_nx', function ($single, $plural, $number, $context, $domain = 'default') {
                return _nx($single, $plural, $number, $context, $domain);
            }),
            new TwigFunction('_n_noop', function ($singular, $plural, $domain = 'default') {
                return _n_noop($singular, $plural, $domain);
            }),
            new TwigFunction('_nx_noop', function ($singular, $plural, $context, $domain = 'default') {
                return _nx_noop($singular, $plural, $context, $domain);
            }),
            new TwigFunction('translate_nooped_plural',
                function ($nooped_plural, $count, $domain = 'default') {
                    return translate_nooped_plural($nooped_plural, $count, $domain);
                }),
            new TwigFunction('pll_e', 'pll_e'),
            new TwigFunction('pll__', 'pll__'),
        ];
    }
}
