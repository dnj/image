<?php

namespace dnj\Image;

use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Local;
use dnj\Filesystem\Tmp;
use dnj\Image\Exceptions\InvalidImageFileException;
use Exception;

class GIF extends GD
{
    /**
     * Save the image to a file.
     */
    public function saveToFile(IFile $file, int $quality = 75): void
    {
        Tmp\File::insureLocal($file, function (Local\File $local) {
            imagegif($this->image, $local->getPath());
        });
    }

    /**
     * Get format of current image.
     */
    public function getExtension(): string
    {
        return 'gif';
    }

    /**
     * Read the image from constructor file.
     *
     * @throws InvalidImageFileException if gd library was unable to load a gif image from the file
     */
    protected function fromFile(): void
    {
        if (!$this->file) {
            throw new Exception();
        }
        $local = Tmp\File::insureLocal($this->file);
        $image = imagecreatefromgif($local->getPath());
        if (false === $image) {
            throw new InvalidImageFileException($local);
        }
        $this->image = $image;
    }
}
