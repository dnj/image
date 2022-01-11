<?php

namespace dnj\Image\Contracts;

use dnj\Filesystem\Contracts\IFile;
use dnj\Image\Color;
use dnj\Image\Exceptions\InvalidImageFileException;

interface IImage
{
    /**
     * Construct an image object with three ways:
     * 	1. pass a file to {$param}
     * 	2. pass other image to {$param}
     * 	3. pass new image width to {$param}.
     *
     * @param IFile|self|int $param
     * @param int|null       $height height of new image in third method
     * @param Color          $bg     background color of new image in third method
     *
     * @throws InvalidImageFileException if file content was corrupted
     */
    public function __construct($param = null, ?int $height = null, ?Color $bg = null);

    /**
     * If image was constructed by a file, this method will return the file.
     */
    public function getFile(): ?IFile;

    /**
     * Save the iamge by overwriting constructor file.
     */
    public function save(int $quality = 75): void;

    /**
     * Resize the image to height.
     * Width will scaled based on height.
     *
     * @param int $height new height in px
     *
     * @return self resized image
     */
    public function resizeToHeight(int $height): self;

    /**
     * Resize the image to width.
     * Height will scaled based on width.
     *
     * @param int $width new width in px
     *
     * @return self resized image
     */
    public function resizeToWidth(int $width): self;

    public function scale(int $scale): self;

    /**
     * Get color of specified pixel.
     */
    public function colorAt(int $x, int $y): Color;

    /**
     * Set color of specified pixel.
     */
    public function setColorAt(int $x, int $y, Color $color): void;

    /**
     * Resize the image to new width and height.
     *
     * @param int $width  in px
     * @param int $height in px
     *
     * @return self resized image
     */
    public function resize(int $width, int $height): self;

    /**
     * Get width of current image.
     *
     * @return int in px
     */
    public function getWidth(): int;

    /**
     * Get height of current image.
     *
     * @return int in px
     */
    public function getHeight(): int;

    /**
     * Get format of current image.
     */
    public function getExtension(): string;

    /**
     * Put anthor image on current image.
     *
     * @param int $x x-coordinate of destination point
     * @param int $y y-coordinate of destination point
     */
    public function paste(self $image, int $x, int $y): void;

    /**
     * Copy a part of image starting at the x,y coordinates with a width and height.
     *
     * @param int $x      x-coordinate of point
     * @param int $y      y-coordinate of point
     * @param int $height
     */
    public function copy(int $x, int $y, int $width, $height): self;

    /**
     * Rotate an image with a given angle
     * The center of rotation is the center of the image, and the rotated image may have different dimensions than the original image.
     *
     * @param float $angle Rotation angle, in degrees. The rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
     * @param Color $bg    specifies the color of the uncovered zone after the rotation
     *
     * @return self Rotated image
     */
    public function rotate(float $angle, Color $bg): self;

    /**
     * Save the image to a file.
     */
    public function saveToFile(IFile $file, int $quality = 75): void;
}
