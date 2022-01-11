<?php

namespace dnj\Image;

use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Exceptions\NotFoundException;
use dnj\Filesystem\Tmp;
use dnj\Image\Contracts\IImage;
use dnj\Image\Exceptions\InvalidImageFileException;
use dnj\Image\Exceptions\UnsupportedFormatException;
use Exception;
use InvalidArgumentException;

abstract class ImageAbstract implements IImage
{
    /**
     * identify and construct an image from its file extension.
     *
     * @throws UnsupportedFormatException if the format was not supported
     */
    public static function fromFormat(IFile $file): self
    {
        switch (strtolower($file->getExtension())) {
            case 'jpeg':
            case 'jpg':
                return new JPEG($file);
            case 'png':
                return new PNG($file);
            case 'gif':
                return new GIF($file);
            case 'webp':
                return new WEBP($file);
            default:
                throw new UnsupportedFormatException($file->getExtension());
        }
    }

    /**
     * identify and construct an image from its file content.
     *
     * @throws UnsupportedFormatException if the format was not supported
     */
    public static function fromContent(IFile $file): self
    {
        $localFile = Tmp\File::insureLocal($file);
        $info = @getimagesize($localFile->getPath());
        if (!$info) {
            throw new UnsupportedFormatException('');
        }
        $image = null;
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $image = new JPEG($localFile);
                break;
            case IMAGETYPE_PNG:
                $image = new PNG($localFile);
                break;
            case IMAGETYPE_GIF:
                $image = new GIF($localFile);
                break;
            case IMAGETYPE_WEBP:
                $image = new WEBP($localFile);
                break;
            default:
                throw new UnsupportedFormatException($info[2]);
        }

        // It's kind of messy but it's because we want to getFile() always return the truth not a tmp file.
        // And besides that our tmp file have no extension and that may cause a problem.
        // Also we could send $file to constructors but it's cause double downloading of remote files.
        $image->file = $file;

        return $image;
    }

    protected ?IFile $file = null;

    /**
     * Construct an image object with three ways:
     * 	1. pass a file to {$param}
     * 	2. pass other image to {$param}
     * 	3. pass new image width to {$param}.
     *
     * @param IFile|IImage|int $param
     * @param int|null         $height height of new image in third method
     * @param Color            $bg     background color of new image in third method
     *
     * @throws NotFoundException if passed file cannot be found
     */
    public function __construct($param = null, ?int $height = null, ?Color $bg = null)
    {
        if ($param instanceof IFile) {
            if (!$param->exists()) {
                throw new NotFoundException($param);
            }
            $this->file = $param;
            $this->fromFile();
        } elseif ($param instanceof IImage) {
            $this->fromImage($param);
        } elseif (is_int($param)) {
            if (!is_int($height)) {
                throw new InvalidArgumentException('height is required');
            }
            if (!$bg) {
                throw new InvalidArgumentException('bg is required');
            }
            $this->createBlank($param, $height, $bg);
        }
    }

    public function getFile(): ?IFile
    {
        return $this->file;
    }

    public function save(int $quality = 75): void
    {
        if (!$this->file) {
            throw new Exception();
        }
        $this->saveToFile($this->file, $quality);
    }

    public function resizeToHeight(int $height): IImage
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;

        return $this->resize($width, $height);
    }

    public function resizeToWidth(int $width): IImage
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;

        return $this->resize($width, $height);
    }

    public function scale(int $scale): IImage
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;

        return $this->resize($width, $height);
    }

    /**
     * Copy anthor image to current image;.
     *
     * @param IImage $other source image
     */
    protected function fromImage(IImage $other): void
    {
        $width = $other->getWidth();
        $height = $other->getHeight();
        $bg = Color::fromRGBA(0, 0, 0, 0);
        $this->createBlank($width, $height, $bg);
        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
                $color = $other->colorAt($x, $y);
                $this->setColorAt($x, $y, $color);
            }
        }
    }

    /**
     * Create new image with provided background color.
     *
     * @return void
     */
    abstract protected function createBlank(int $width, int $height, Color $bg);

    /**
     * Read the image from constructor file.
     *
     * @throws InvalidImageFileException if the format was not supported
     */
    abstract protected function fromFile(): void;
}
