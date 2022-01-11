<?php

namespace dnj\Image;

use dnj\Image\Exceptions\InvalidColorRangeException;

class Color
{
    /**
     * Construct a RGB color.
     *
     * @param int $r red, value: 0-255
     * @param int $g green, value: 0-255
     * @param int $b blue, value: 0-255
     */
    public static function fromRGB(int $r, int $g, int $b): self
    {
        if ($r < 0 or $r > 255) {
            throw new InvalidColorRangeException('red is '.$r);
        }
        if ($g < 0 or $g > 255) {
            throw new InvalidColorRangeException('green is '.$g);
        }
        if ($b < 0 or $b > 255) {
            throw new InvalidColorRangeException('blue is '.$b);
        }
        $color = new self();
        $color->r = $r;
        $color->g = $g;
        $color->b = $b;
        $color->a = 1;

        return $color;
    }

    /**
     * Construct a RGB color with alpha channel.
     *
     * @param int   $r red, value: 0-255
     * @param int   $g green, value: 0-255
     * @param int   $b blue, value: 0-255
     * @param float $a blue, value: 0-1
     */
    public static function fromRGBA(int $r, int $g, int $b, float $a): self
    {
        if ($a < 0 or $a > 1) {
            throw new InvalidColorRangeException('alpha is '.$a);
        }
        $color = self::fromRGB($r, $g, $b);
        $color->a = $a;

        return $color;
    }

    /** @var int red, value: 0-255 */
    private $r;

    /** @var int green, value: 0-255 */
    private $g;

    /** @var int blue, value: 0-255 */
    private $b;

    /** @var float alpha, value: 0-1 */
    private $a;

    /**
     * @return array{0:int,1:int,2:int}
     */
    public function toRGB(): array
    {
        return [$this->r, $this->g, $this->b];
    }

    /**
     * @return array{0:int,1:int,2:int,3:float}
     */
    public function toRGBA(): array
    {
        return [$this->r, $this->g, $this->b, $this->a];
    }
}
