<?php

namespace KoalaPress\FlexibleContent;

use Illuminate\Support\Str;
use KoalaPress\Support\Helper\NamingHelper;
use Throwable;

class Module
{
    /**
     * @var string|null
     *
     * The name for the module, used in the admin interface.
     */
    public static ?string $name = null;

    /**
     * @var string|null
     *
     * The label for the module, used in the admin interface.
     */
    public static ?string $label = null;

    /**
     * The field group key for the ACF fields.
     *
     * @var string|null
     */
    public static ?string $fieldGroup = null;

    /**
     * @var string
     *
     * The icon for the module, used in the admin interface.
     * @see https://developer.wordpress.org/resource/dashicons/
     */
    public static string $icon = 'layout';

    /**
     * Module constructor.
     *
     * @param array $data
     */
    public function __construct(private array $data = [])
    {
    }

    /**
     * Get the data for the module.
     *
     * @return array
     */
    private function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the field group key for the ACF fields.
     *
     * @return string|null
     */
    public static function getFieldGroup(): ?string
    {
        return static::$fieldGroup;
    }

    /**
     * Get the name of the module.
     *
     * @return string
     */
    public static function getName(): string
    {
        return static::$name ?? self::getShortName();
    }

    /**
     * Get the label of the module.
     *
     * @return string
     */
    public static function getLabel(): string
    {
        return static::$label ?? self::getShortName();
    }

    /**
     * Get the short name for the module.
     *
     * @return string
     */
    private static function getShortName(): string
    {
        return NamingHelper::getShortName(
            className: static::class,
            prefix: 'Module'
        );
    }

    /**
     * Get the view name for the module.
     *
     * @return string
     */
    public static function getViewName(): string
    {
        return 'module.' . Str::kebab(NamingHelper::getShortName(static::class));
    }

    /**
     * Render the module view.
     *
     * @return string
     * @throws Throwable
     */
    public function render(): string
    {
        return view(static::getViewName(), $this->getData())
            ->render();
    }

}
