<?php

namespace KoalaPress\Template;


use Brain\Hierarchy\Finder\ByFolders;
use Brain\Hierarchy\QueryTemplate;
use Illuminate\Support\ServiceProvider;
use KoalaPress\Context\Context;
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
            $viewName = basename($template, '.twig');
            $viewName = basename($viewName, '.blade.php');

            $context = new Context();

            echo view('templates.' . $viewName, [
                $context->getAll(),
            ])->render();

            exit();
        });
    }
}
