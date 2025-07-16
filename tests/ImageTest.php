<?php

use KoalaPress\Image\Image;

beforeEach(function () {
    $this->image = new Image();
});

describe('Image', function () {
    it('returns empty array for invalid image', function () {
        expect($this->image->getSourceset(null))->toBeArray()->toBeEmpty();
    });

    it('returns correct data for a normal image', function () {
        $imageArr = [
            'filename' => 'photo.jpg',
            'caption' => 'A caption',
            'alt' => 'Alt text',
            'url' => 'http://example.com/photo.jpg',
            'width' => 1000,
            'height' => 500,
            'sizes' => [
                'variable_500' => 'http://example.com/photo-500.jpg',
                'variable_1000' => 'http://example.com/photo-1000.jpg',
            ],
        ];
        $result = $this->image->getSourceset($imageArr, 'variable');
        expect($result)
            ->toHaveKey('srcset')
            ->and($result['alt'])->toBe('Alt text')
            ->and($result['caption'])->toBe('A caption')
            ->and($result['width'])->toBe(1000)
            ->and($result['height'])->toBe(500);
    });

    it('returns correct data for SVG image', function () {
        // Mock WordPress and XML functions
        if (!function_exists('acf_get_attachment')) {
            function acf_get_attachment($id) {
                return [
                    'id' => 123,
                    'filename' => 'icon.svg',
                    'caption' => 'SVG caption',
                    'alt' => '',
                    'url' => 'http://example.com/icon.svg',
                ];
            }
        }
        if (!function_exists('wp_get_original_image_path')) {
            function wp_get_original_image_path($id) {
                return __DIR__ . '/fixtures/icon.svg';
            }
        }
        if (!function_exists('get_bloginfo')) {
            function get_bloginfo($key) {
                return $key === 'name' ? 'SiteName' : 'SiteDesc';
            }
        }
        // Create a fake SVG file
        $svgContent = '<svg viewBox="0 0 100 50" width="100" height="50"></svg>';
        @mkdir(__DIR__ . '/fixtures');
        file_put_contents(__DIR__ . '/fixtures/icon.svg', $svgContent);

        $image = new Image();
        $result = $image->getSourceset(123);
        expect($result)
            ->toHaveKey('ratio')
            ->and($result['width'])->toBe(100)
            ->and($result['height'])->toBe(50)
            ->and($result['src'])->toBe('http://example.com/icon.svg');

        // Cleanup
        unlink(__DIR__ . '/fixtures/icon.svg');
        rmdir(__DIR__ . '/fixtures');
    });

    it('returns default alt if alt is missing', function () {
        if (!function_exists('get_bloginfo')) {
            function get_bloginfo($key) {
                return $key === 'name' ? 'SiteName' : 'SiteDesc';
            }
        }
        $imageArr = [
            'filename' => 'photo.jpg',
            'caption' => 'A caption',
            'alt' => '',
            'url' => 'http://example.com/photo.jpg',
            'width' => 1000,
            'height' => 500,
            'sizes' => [
                'variable_500' => 'http://example.com/photo-500.jpg',
                'variable_1000' => 'http://example.com/photo-1000.jpg',
            ],
        ];
        $result = $this->image->getSourceset($imageArr, 'variable');
        expect($result['alt'])->toContain('SiteName');
    });
});
