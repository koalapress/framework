<?php

namespace KoalaPress\Template;


use Brain\Hierarchy\Finder\ByFolders;
use Brain\Hierarchy\QueryTemplate;
use Illuminate\Support\ServiceProvider;
use Throwable;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws Throwable
     */
    public function boot(): void
    {
        add_action('template_redirect', function () {
            $templatePath = $this->app->resourcePath('views/templates');

            $finder = new ByFolders(
                [
                    $templatePath,
                ],
                'twig',
                'blade.php'
            );

            $queryTemplate = new QueryTemplate($finder);
            $template = $queryTemplate->findTemplate(null, false);
            $view_name = basename($template, '.twig');
            $view_name = basename($view_name, '.blade.php');

            echo view('templates.' . $view_name)->render();

            exit();
        });
    }
}
