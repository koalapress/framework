<?php

namespace KoalaPress\Image;

use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image as SpatieImage;
use Tracy\Debugger;

class Image
{
    protected $id;
    protected $filename;
    protected $metadata;
    protected $file;
    protected $folder;
    protected $crops_folder;
    protected $disk;

    public const IMAGE_EXTENSION = 'avif';

    public function __construct($id, $metadata = null)
    {
        if ($id === null) {
            throw new \InvalidArgumentException('Image ID cannot be null');
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

        $sizes = \wp_get_additional_image_sizes();

        $widths = config('image-sizes.responsive.widths', []);

        if (empty($widths)) {
            throw new \RuntimeException('No responsive image widths configured');
        }

        $this->disk->makeDirectory($this->crops_folder, 0777);

        foreach ($sizes as $key => $size) {
            foreach ($widths as $width) {
                if ($size['width'] >= $width || config('image-sizes.responsive.upscale', false)) {
                    SpatieImage::load($this->disk->path($this->file))
                        ->width($width)
                        ->save($this->getCropFilePath($key, $width));
                }
            }
        }
    }

    public function delete()
    {
        $this->disk->deleteDirectory($this->crops_folder);
    }

    protected function getCropFilePath($size, $width)
    {
        return \wp_upload_dir()['basedir'] . '/' . $this->crops_folder . '/' . $this->filename . '-' . $size . '-' . $width . '.' . self::IMAGE_EXTENSION;
    }

    public function getUrl()
    {
        return $this->disk->url($this->file);
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
        $this->folder = $image_info['dirname'];
        $this->crops_folder = $this->folder . '/' . $this->id;
    }
}
