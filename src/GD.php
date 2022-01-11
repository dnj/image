<?php

namespace dnj\Image;

use dnj\Filesystem\Contracts\IFile;
use dnj\Filesystem\Local;
use dnj\Filesystem\Tmp;
use dnj\Image\Contracts\IImage;
use dnj\Image\Exceptions\InvalidImageFileException;
use dnj\Image\Exceptions\UnsupportedFormatException;
use Exception;
use GdImage;

class GD extends ImageAbstract
{
    protected GdImage $image;

    /**
     * Construct an image object with three ways:
     * 	1. pass a file to {$param}
     * 	2. pass other image to {$param}
     * 	3. pass new image width to {$param}
     *  4. pass a GD image resouce.
     *
     * @param IFile|IImage|int|GdImage $param
     * @param int|null                 $height height of new image in third method
     * @param Color                    $bg     background color of new image in third method
     */
    public function __construct($param = null, ?int $height = null, ?Color $bg = null)
    {
        if ($param instanceof GdImage) {
            $this->fromGDImage($param);
        } else {
            /*
             * @var IFile|IImage|int $param
             */
            parent::__construct($param, $height, $bg);
        }
    }

    /**
     * Save the image to a file.
     */
    public function saveToFile(IFile $file, int $quality = 75): void
    {
        Tmp\File::insureLocal($file, function (Local\File $local) {
            imagegd($this->image, $local->getPath());
        });
    }

    public function getWidth(): int
    {
        return imagesx($this->image);
    }

    public function getHeight(): int
    {
        return imagesy($this->image);
    }

    public function resize(int $width, int $height): self
    {
        $color = Color::fromRGBA(0, 0, 0, 0);
        $new = new static($width, $height, $color);
        imagecopyresampled($new->image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        return $new;
    }

    public function colorAt(int $x, int $y): Color
    {
        $rgb = imagecolorat($this->image, $x, $y);
        if (false === $rgb) {
            throw new Exception('imagecolorat failed');
        }
        $colors = imagecolorsforindex($this->image, $rgb);
        $colors['alpha'] = round((127 - $colors['alpha']) / 127);

        return Color::fromRGBA($colors['red'], $colors['green'], $colors['blue'], $colors['alpha']);
    }

    public function setColorAt(int $x, int $y, Color $color): void
    {
        $colors = $color->toRGBA();
        $colors[3] = intval(127 - ($colors[3] * 127));
        $rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
        if (false === $rgba) {
            throw new Exception('imagecolorallocatealpha failed');
        }
        imagesetpixel($this->image, $x, $y, $rgba);
    }

    public function getExtension(): string
    {
        return 'gd';
    }

    public function paste(IImage $image, int $x, int $y, float $opacity = 1): void
    {
        if (!$image instanceof self) {
            throw new UnsupportedFormatException('non-GD images not supported');
        }
        $width = $image->getWidth();
        $height = $image->getHeight();
        if ($image instanceof PNG) {
            $cut = imagecreatetruecolor($width, $height);
            if (false === $cut) {
                throw new Exception('imagecreatetruecolor failed');
            }
            imagecopy($cut, $this->image, 0, 0, $x, $y, $width, $height);
            imagecopy($cut, $image->image, 0, 0, 0, 0, $width, $height);
            imagecopymerge($this->image, $cut, $x, $y, 0, 0, $width, $height, intval($opacity * 100));
        } else {
            imagecopy($this->image, $image->image, $x, $y, 0, 0, $width, $height);
        }
    }

    /**
     * Copy a part of image starting at the x,y coordinates with a width and height.
     *
     * @param int $x x-coordinate of point
     * @param int $y y-coordinate of point
     */
    public function copy(int $x, int $y, int $width, $height): self
    {
        $new = new static($width, $height, Color::fromRGBA(0, 0, 0, 0));
        imagecopy($new->image, $this->image, 0, 0, $x, $y, $width, $height);

        return $new;
    }

    public function rotate(float $angle, Color $bg): self
    {
        $colors = $bg->toRGBA();
        $colors[3] = intval(127 - ($colors[3] * 127));
        $rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
        if (false === $rgba) {
            throw new Exception('imagecolorallocatealpha failed');
        }
        $rotated = imagerotate($this->image, $angle, $rgba);
        if (false === $rotated) {
            throw new Exception('imagerotate failed');
        }

        return new static($rotated);
    }

    public function __destruct()
    {
        if (null !== $this->image) {
            imagedestroy($this->image);
        }
    }

    /**
     * Read the image from constructor file.
     *
     * @throws InvalidImageFileException if gd library was unable to load image from the file
     */
    protected function fromFile(): void
    {
        if (!$this->file) {
            throw new Exception();
        }
        $local = Tmp\File::insureLocal($this->file);
        $image = imagecreatefromgd($local->getPath());
        if (false === $image) {
            throw new InvalidImageFileException($local);
        }
        $this->image = $image;
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
        imagefilledrectangle($this->image, 0, 0, $width, $height, $rgba);
    }

    /**
     * Copy anthor image to current image;.
     *
     * @param IImage $other source image
     */
    protected function fromImage(IImage $other): void
    {
        if ($other instanceof self) {
            $this->image = $other->image;
        } else {
            parent::fromImage($other);
        }
    }

    protected function fromGDImage(GdImage $image): void
    {
        $this->image = $image;
    }
}
