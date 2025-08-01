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
        $expectedSizes = ['full'];
        $expectedSizes += \get_intermediate_image_sizes();
        $expectedSizes = array_unique($expectedSizes);
        $full_was_generated = false;

        $sizes = [];

        foreach ($expectedSizes as $size) {
            $file_src = $this->folder . '/' . ($this->metadata['sizes'][$size]['file'] ?? $this->basename);
            $use_full = $size === 'full' || !isset($this->metadata['sizes'][$size]['file']);
            $generate_crops = $use_full && !$full_was_generated || !$use_full;

            $configured_size = config('image-sizes.sizes.' . $size, [
                'width' => $this->metadata['width'],
                'height' => $this->metadata['height'],
                'crop' => true,
            ]);

            $ratio = $configured_size['height'] / $configured_size['width'];

            $crops = collect(config('image-sizes.responsive.widths', []))
                ->map(function ($width) use ($size, $use_full, $ratio) {
                    return [
                      'file' => $this->getCropFilePath($use_full ? 'full' : $size, $width),
                      'width' => $width,
                      'height' => (int)round($width * $ratio),
                      'upscaled' => $width > $this->metadata['width'],
                    ];
                });

            $sizes[$size] = [
              'file_src' => $file_src,
              'crops' => $crops->toArray(),
              'generate_crops' => $generate_crops,
            ];

            if ($use_full) {
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
