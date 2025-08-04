<?php

namespace KoalaPress\Image;

use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image as SpatieImage;
use Spatie\Image\Enums\Fit;
use Tracy\Debugger;

class Image
{
    protected $id;
    protected $filename;
    protected $basename;
    protected $metadata;
    protected $file;
    protected $folder;
    protected $crops_folder;
    protected $disk;
    protected $allSizes;

    public const IMAGE_EXTENSION = 'avif';

    public function __construct($id, $metadata = null)
    {
        if ($id === null) {
            throw new \InvalidArgumentException('Image ID cannot be null');
        }

        if (empty(config('image-sizes.responsive.widths', []))) {
            throw new \RuntimeException('No responsive image widths configured');
        }

        if (empty(config('image-sizes.sizes', []))) {
            throw new \RuntimeException('No responsive image sizes configured');
        }

        $this->id = $id;

        $upload_dir = \wp_upload_dir();
        $this->disk = Storage::build([
          'driver' => 'local',
          'root' => $upload_dir['basedir'],
          'url' => $upload_dir['baseurl'],
        ]);

        $this->getImageMetadata($metadata);
        $this->getImageData();
        $this->getAllSizes();

        if (!$this->disk->exists($this->file)) {
            throw new \RuntimeException('Image not found in storage: ' . $this->file);
        }
    }

    public function generateCrops()
    {
        if (count(\wp_get_missing_image_subsizes($this->id))) {
            return;
        }

        $this->delete();
        $this->disk->makeDirectory($this->crops_folder);

        foreach ($this->allSizes as $sizeInfo) {
            if (!$sizeInfo['generate_crops']) {
                continue;
            }

            foreach ($sizeInfo['crops'] as $crop) {
                $this->generateCrop($sizeInfo['file_src'], $crop['width'], $crop['height'], $crop['file']);
            }
        }
    }

    public function getSrcset($sizeName = 'full')
    {
        if (!isset($this->allSizes[$sizeName])) {
            throw new \InvalidArgumentException('Image size not found: ' . $sizeName);
        }

        $sizeInfo = $this->allSizes[$sizeName];

        return collect($sizeInfo['crops'])
            ->map(function ($crop) {
                if ($crop['upscaled'] && !config('image-sizes.responsive.upscale', false)) {
                    return;
                }
                return $this->disk->url($crop['file']) . ' ' . $crop['width'] . 'w';
            })
            ->filter()
            ->implode(', ');
    }

    public function getAspectRatio($sizeName = 'full')
    {
        if (!isset($this->allSizes[$sizeName])) {
            throw new \InvalidArgumentException('Image size not found: ' . $sizeName);
        }

        return $this->allSizes[$sizeName]['css_ratio'];
    }

    public function getUrl()
    {
        return $this->disk->url($this->file);
    }

    public function delete()
    {
        $this->disk->deleteDirectory($this->crops_folder);
    }

    protected function generateCrop($src_file, $width, $height, $destination)
    {
        if ($src_file === null) {
            return;
        }

        SpatieImage::load($this->disk->path($src_file))
            ->fit(Fit::Crop, $width, $height)
            ->optimize()
            ->save($this->disk->path($destination));
    }

    protected function getAllSizes()
    {
        $expectedSizes['full'] = [
          'width' => $this->metadata['width'],
          'height' => $this->metadata['height'],
        ];
        $expectedSizes = array_merge(
            $expectedSizes,
            \wp_get_attachment_metadata($this->id)['sizes'] ?? [],
            \wp_get_additional_image_sizes()
        );
        $expectedSizes = collect($expectedSizes)
            ->map(function ($size_info, $size_name) {
                return [
                    'file_src' => $this->folder . '/' . ($this->metadata['sizes'][$size_name]['file'] ?? $this->basename),
                    'width' => $size_info['width'],
                    'height' => $size_info['height'],
                    'ratio' => $size_info['height'] / $size_info['width'],
                    'use_full' => $size_name === 'full' || !isset($this->metadata['sizes'][$size_name]['file']),
                ];
            })
            ->toArray();

        $full_was_generated = false;
        $sizes = [];

        foreach ($expectedSizes as $size => $size_info) {
            $crops = collect(config('image-sizes.responsive.widths', []))
                ->map(function ($width) use ($size, $size_info) {
                    return [
                      'file' => $this->getCropFilePath($size_info['use_full'] ? 'full' : $size, $width),
                      'width' => $width,
                      'height' => (int)round($width * $size_info['ratio']),
                      'upscaled' => $width > $size_info['width'],
                    ];
                });

            $sizes[$size] = [
              'file_src' => $size_info['file_src'],
              'crops' => $crops->toArray(),
              'css_ratio' => '1 / ' . $size_info['ratio'],
              'generate_crops' => $size_info['use_full'] && !$full_was_generated || !$size_info['use_full'],
            ];

            if ($size_info['use_full']) {
                $full_was_generated = true;
            }
        }

        $this->allSizes = $sizes;
    }

    protected function getCropFilePath($size, $width)
    {
        return $this->crops_folder . '/' . $this->filename . '-' . $size . '-' . $width . '.' . self::IMAGE_EXTENSION;
    }

    protected function getImageMetadata($metadata)
    {
        $metadata = $metadata ?? \wp_get_attachment_metadata($this->id);
        if (!$metadata) {
            throw new \RuntimeException('Image metadata not found for ID: ' . $this->id);
        }
        $this->metadata = $metadata;
    }

    protected function getImageData()
    {
        $this->file = $this->metadata['file'];
        $image_info = pathinfo($this->file);
        $this->filename = $image_info['filename'];
        $this->basename = $image_info['basename'];
        $this->folder = $image_info['dirname'];
        $this->crops_folder = $this->folder . '/' . $this->id;
    }
}
