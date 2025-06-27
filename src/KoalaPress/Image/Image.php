<?php

namespace KoalaPress\Image;

class Image
{
    public function getSourceset($image = null, $size = 'variable', $lazyload = false): ?array
    {
        // todo: get default size from config
        // todo: move $lazyload to view?

        if (is_numeric($image) || is_string($image)) {
            $image = acf_get_attachment($image);
        }

        if (!$image || !isset($image['filename'])) {
            return [];
        }

        if (str_ends_with($image['filename'], '.svg')) {
            $svg = simplexml_load_file(wp_get_original_image_path($image['id']));

            if ($svg) {
                $width = (int)explode(' ', $svg->attributes()->viewBox)[2];
                $height = (int)explode(' ', $svg->attributes()->viewBox)[3];

                if ($width == 0) {
                    $width = (int)$svg->attributes()->width;
                    $height = (int)$svg->attributes()->height;
                }

                if ($width > 0 && $height > 0) {
                    $ratioPadding = ($height / $width) * 100;
                }
            }

            return [
                'ratio' => number_format($ratioPadding ?? 100, 2, '.', ','),
                'caption' => $image['caption'],
                'alt' => $image['alt'] ?: get_bloginfo('name') . ' – ' . get_bloginfo('description'),
                'src' => $image['url'],
                'thumb' => $image['url'],
                'lazyload' => $lazyload,
                'width' => $width ?? '100%',
                'height' => $height ?? 'auto',
            ];
        }

        $ratio = explode('x', $size);

        if ($size === 'variable') { // todo: get from config
            $ratioPadding = $image['width'] != 0 ? ($image['height'] / $image['width']) * 100 : 0;
        } else {
            $ratioPadding = ($ratio[1] / $ratio[0]) * 100;
        }

        $imageSizes = $image['sizes'];

        $imageBySize = array_filter($imageSizes, function ($k) use ($size, $image) {
            return preg_match('/^' . $size . '/', $k) && !preg_match('/(-width|-height)/i', $k) && (int)explode('_',
                    $k)[1] <= $image['width'];
        }, ARRAY_FILTER_USE_KEY);

        $srcSet = implode(', ', array_map(
            function ($v, $k) use ($image) {
                return sprintf('%s %sw', $v, explode('_', $k)[1]);
            },
            $imageBySize,
            array_keys($imageBySize)
        ));

        return [
            'ratio' => number_format($ratioPadding, 2, '.', ','),
            'srcset' => $srcSet,
            'caption' => $image['caption'],
            'alt' => $image['alt'] ?: get_bloginfo('name') . ' – ' . get_bloginfo('description'),
            'src' => $imageBySize[array_key_last($imageBySize)],
            'lazyload' => $lazyload,
            'width' => $image['width'] && $size === 'variable' ? $image['width'] : '100%',
            'height' => $image['height'] && $size === 'variable' ? $image['height'] : 'auto',
        ];
    }
}
