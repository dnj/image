<?php

namespace dnj\Image;

use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Local;
use dnj\Filesystem\Tmp;
use dnj\Image\Exceptions\InvalidImageFileException;
use Exception;

class JPEG extends GD
{
    /**
     * Save the image to a file.
     */
    public function saveToFile(IFile $file, int $quality = 75): void
    {
        Tmp\File::insureLocal($file, function (Local\File $local) use ($quality) {
            imagejpeg($this->image, $local->getPath(), $quality);
        });
    }

    /**
     * Get format of current image.
     */
    public function getExtension(): string
    {
        return 'jpg';
    }

    /**
     * Read the image from constructor file.
     *
     * @throws InvalidImageFileException if gd library was unable to load a jpeg image from the file
     */
    protected function fromFile(): void
    {
        if (!$this->file) {
            throw new Exception();
        }
        $local = Tmp\File::insureLocal($this->file);
        $image = imagecreatefromjpeg($local->getPath());
        if (false === $image) {
            throw new InvalidImageFileException($local);
        }
        $this->image = $image;
    }
}
