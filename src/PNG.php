<?php

namespace dnj\Image;

use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Local;
use dnj\Filesystem\Tmp;
use dnj\Image\Exceptions\InvalidImageFileException;
use Exception;

class PNG extends GD
{
    /**
     * Save the image to a file.
     */
    public function saveToFile(IFile $file, int $quality = 75): void
    {
        Tmp\File::insureLocal($file, function (Local\File $local) use ($quality) {
            imagepng($this->image, $local->getPath(), intval((100 - $quality) / 10));
        });
    }

    /**
     * Get format of current image.
     */
    public function getExtension(): string
    {
        return 'png';
    }

    /**
     * Create new image with provided background color.
     */
    protected function createBlank(int $width, int $height, Color $bg): void
    {
        $image = imagecreatetruecolor($width, $height);
        if (false === $image) {
            throw new Exception('imagecreatetruecolor failed');
        }
        $this->image = $image;
        $colors = $bg->toRGBA();
        $colors[3] = intval(127 - ($colors[3] * 127));
        $rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
        if (false === $rgba) {
            throw new Exception('imagecolorallocatealpha failed');
        }
        imagecolortransparent($this->image, $rgba);
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);

        imagefilledrectangle($this->image, 0, 0, $width, $height, $rgba);
    }

    /**
     * Read the image from constructor file.
     *
     * @throws InvalidImageFileException if gd library was unable to load a png image from the file
     */
    protected function fromFile(): void
    {
        if (!$this->file) {
            throw new Exception();
        }
        $local = Tmp\File::insureLocal($this->file);
        $image = imagecreatefrompng($local->getPath());
        if (false === $image) {
            throw new InvalidImageFileException($local);
        }
        $this->image = $image;
    }
}
