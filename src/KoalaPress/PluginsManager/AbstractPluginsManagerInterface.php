<?php

namespace KoalaPress\PluginsManager;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use KoalaPress\PluginsManager\Contracts\PluginsManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractPluginsManagerInterface implements PluginsManagerInterface
{
    /**
     * Path to the theme's composer.lock file.
     *
     * @var string
     */
    protected string $composerLockPath;

    /**
     * File that marks a plugin as managed by the theme.
     *
     * @var string
     */
    protected string $markerFile = '.managed-by-theme';

    /**
     * Filesystem instance for file operations.
     *
     * @var Filesystem
     */
    protected $fs;

    /**
     * The slug of the current theme.
     *
     * @var string
     */
    protected string $themeSlug;

    /**
     * The list of installed plugins freshly installed by the theme.
     *
     * @var array
     */
    protected $installedPlugins = [];

    /**
     * AbstractPluginsManager constructor.
     *
     * Initializes the manager with the theme's composer.lock path and filesystem instance.
     */
    public function __construct()
    {
        $themeDir = get_template_directory();
        $this->composerLockPath = $themeDir . '/composer.lock';
        $this->fs = app('files');
        $this->themeSlug = get_template();
    }

    /**
     * Abstract method to define the source path for plugins.
     *
     * @return string
     */
    abstract protected function sourcePath(): string;

    /**
     * Abstract method to define the target path for plugins.
     *
     * @return string
     */
    abstract protected function targetPath(): string;

    /**
     * Abstract method to define the composer type for plugins.
     *
     * @return string
     */
    abstract protected function composerType(): string;

    /**
     * Synchronizes the plugins with the current theme's bundled plugins.
     *
     * This method reads the `composer.lock` file to determine which plugins
     * are required, copies them from the theme's bundled plugins directory
     * to the WordPress plugins directory, and marks them as managed by the theme.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sync(): void
    {
        if (!$this->fs->exists($this->composerLockPath)) {
            return;
        }

        $lock = json_decode($this->fs->get($this->composerLockPath), true);
        $packages = $lock['packages'] ?? [];

        if (!app()->isProduction()) {
            $packages = array_merge($packages, $lock['packages-dev'] ?? []);
        }

        // get all required plugins based on the composer type
        $required = collect($packages)
            ->filter(fn($pkg) => $pkg['type'] === $this->composerType())
            ->mapWithKeys(fn($pkg) => [
                basename($pkg['name']) => [
                    'slug' => basename($pkg['name']),
                    'version' => $pkg['version'],
                ]
            ]);

        // Copy and mark required plugins
        $required->each(function ($meta, $slug) {
            $src = $this->sourcePath() . '/' . $slug;
            $dst = $this->targetPath() . '/' . $slug;
            $marker = $dst . '/' . $this->markerFile;

            if (!$this->fs->isDirectory($src)) {
                return;
            }

            $shouldCopy = false;

            if (!$this->fs->isDirectory($dst)) {
                $this->installedPlugins[] = $slug;
                $shouldCopy = true;
            } elseif ($this->fs->exists($marker)) {
                $info = json_decode($this->fs->get($marker), true);
                $currentVersion = $info['version'] ?? null;

                if ($currentVersion !== $meta['version']) {
                    $this->fs->deleteDirectory($dst);
                    $shouldCopy = true;
                }
            }

            if ($shouldCopy) {
                $this->fs->copyDirectory($src, $dst);
                $this->fs->put($marker, json_encode([
                    'installed_by' => $this->themeSlug,
                    'source' => 'composer',
                    'version' => $meta['version'],
                ], JSON_PRETTY_PRINT));
            }
        });

        // Remove plugins that are no longer required
        $dirs = collect($this->fs->directories($this->targetPath()));

        $dirs->each(function ($dir) use ($required) {
            $slug = basename($dir);
            $marker = $dir . '/' . $this->markerFile;

            if ($this->fs->exists($marker) && !$required->has($slug)) {
                $this->fs->deleteDirectory($dir);
            }
        });
    }

    /**
     * Returns an array of slugs for plugins managed by the theme.
     *
     * This method reads the `composer.lock` file and extracts the slugs of
     * plugins that are managed by the theme, based on their type.
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws FileNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function getManagedPluginSlugs(): array
    {
        if (!$this->fs->exists($this->composerLockPath)) {
            return [];
        }

        $lock = json_decode($this->fs->get($this->composerLockPath), true);
        $packages = $lock['packages'] ?? [];

        if (!app()->isProduction()) {
            $packages = array_merge($packages, $lock['packages-dev'] ?? []);
        }

        return collect($packages)
            ->filter(fn($pkg) => $pkg['type'] === $this->composerType())
            ->map(fn($pkg) => basename($pkg['name']))
            ->values()
            ->all();
    }

    /**
     * Returns the list of installed plugins that were freshly installed by the theme.
     *
     * @return array
     */
    public function getInstalledPlugins(): array
    {
        return $this->installedPlugins;
    }
}
