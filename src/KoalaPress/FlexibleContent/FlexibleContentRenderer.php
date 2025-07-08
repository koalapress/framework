<?php

namespace KoalaPress\FlexibleContent;

use Illuminate\Support\Facades\View;

class FlexibleContentRenderer
{
    /**
     * Render a flexible content field (ACF) as module views.
     *
     * @param string|array|null $content ACF field name or flexible content array
     * @param int|null $postId Optional post ID if $content is a field name
     * @param string $viewPrefix View namespace (e.g. "module.")
     * @return string Rendered HTML
     */
    public static function render(string|array|null $content, ?int $postId = null, string $viewPrefix = 'module.'): string
    {
        if(!function_exists('get_field')) {
            return '<!-- ACF function get_field() not available. Ensure ACF is installed and activated. -->';
        }

        // Allow passing flexible content array directly
        $layouts = is_array($content) ? $content : get_field($content, $postId ?? get_the_ID());

        if (!is_array($layouts)) {
            return '';
        }

        $output = '';

        foreach ($layouts as $index => $layout) {
            $layoutName = $layout['acf_fc_layout'] ?? null;

            if (!$layoutName) {
                $output .= "<!-- Missing layout name in block {$index} -->";
                continue;
            }

            $view = $viewPrefix . $layoutName;

            if (!View::exists($view)) {
                $output .= "<!-- View [$view] not found for layout [$layoutName] -->";
                continue;
            }

            // Optional: pass section index if needed
            $layout['section_id'] ??= 'section-' . ($index + 1);

            // Render the view
            $output .= View::make($view, $layout)->render();
        }

        return $output;
    }
}
