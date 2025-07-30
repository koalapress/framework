<?php

namespace KoalaPress\Acf;

use Illuminate\Support\Str;

class AcfWysiwygCustomizer
{
    /**
     * Constructor to initialize the WYSIWYG customizer.
     *
     * @return void
     */
    public function __construct()
    {
        // cutomize WYSIWYG buttons
        add_filter(
            'acf/fields/wysiwyg/toolbars',
            [$this, 'customizeToolbars'],
            10,
            1
        );

        // set options for WYSIWYG format select
        add_filter(
            'tiny_mce_before_init',
            [$this, 'customizeFormatSelect'],
            10,
            1
        );
    }

    /**
     * Customize the WYSIWYG toolbars.
     *
     * @return array
     */
    public function customizeToolbars($toolbars)
    {
        $newToolbars = config('acf.wysiwyg.toolbars');

        foreach ($newToolbars as $key => $newToolbar) {
            $toolbars[Str::ucfirst($key)][1] = $newToolbar;
        }

        return $toolbars;
    }

    /**
     * Customize the format select options for the WYSIWYG editor.
     *
     * @param array $settings
     * @return array
     */
    public function customizeFormatSelect($settings)
    {
        $settings['block_formats'] = config('acf.wysiwyg.format_select', 'Absatz=p;Überschrift 2=h2;Überschrift 3=h3;Überschrift 4=h4;Überschrift 5=h5;Überschrift 6=h6');

        return $settings;
    }
}
