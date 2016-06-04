<?php
namespace Atans\Memobird\Utils;

/**
 * GDIndexedColorConverter
 * A converter to convert an image resource into indexed color mode.
 * Licensed under The MIT License
 *
 * @author    Jeremy Yu
 * @copyright Copyright 2014 Jeremy Yu
 * @license   https://github.com/ccpalettes/gd-indexed-color-converter/blob/master/LICENSE
 **/

/**
 * Index Color Mode Converter Class
 * Convert an image to indexed color mode.
 */
class GDIndexedColorConverter
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Convert an image resource to indexed color mode.
     * The original image resource will not be changed, a new image resource will be created.
     *
     * @param resource $im The image resource
     * @param array $palette    The color palette
     * @param float $dither     The Floyd–Steinberg dither amount, value is between 0 and 1 and default value is 0.75
     * @return resource The image resource in indexed colr mode.
     */
    public function convertToIndexedColor($im, $palette, $dither = 0.75)
    {
        $newPalette = array();
        foreach ($palette as $paletteColor) {
            $newPalette[] = array(
                'rgb' => $paletteColor,
                'lab' => $this->RGBtoLab($paletteColor),
            );
        }

        $width  = imagesx($im);
        $height = imagesy($im);

        $newImage = $this->floydSteinbergDither($im, $width, $height, $newPalette, $dither);

        return $newImage;
    }

    /**
     * Apply Floyd–Steinberg dithering algorithm to an image.
     * http://en.wikipedia.org/wiki/Floyd%E2%80%93Steinberg_dithering
     *
     * @param resource $im The image resource
     * @param integer $width    The width of an image
     * @param integer $height   The height of an image
     * @param array $palette    The color palette
     * @param float $amount     The dither amount(value is between 0 and 1)
     * @return array The pixels after applying Floyd–Steinberg dithering
     */
    private function floydSteinbergDither($im, $width, $height, &$palette, $amount)
    {
        $newImage = imagecreatetruecolor($width, $height);

        for ($i = 0; $i < $height; $i++) {
            if ($i === 0) {
                $currentRowColorStorage = array();
            } else {
                $currentRowColorStorage = $nextRowColorStorage;
            }

            $nextRowColorStorage = array();

            for ($j = 0; $j < $width; $j++) {
                if ($i === 0 && $j === 0) {
                    $color = $this->getRGBColorAt($im, $j, $i);
                } else {
                    $color = $currentRowColorStorage[$j];
                }
                $closestColor = $this->getClosestColor(array('rgb' => $color), $palette, 'rgb');
                $closestColor = $closestColor['rgb'];

                if ($j < $width - 1) {
                    if ($i === 0) {
                        $currentRowColorStorage[$j + 1] = $this->getRGBColorAt($im, $j + 1, $i);
                    }
                }
                if ($i < $height - 1) {
                    if ($j === 0) {
                        $nextRowColorStorage[$j] = $this->getRGBColorAt($im, $j, $i + 1);;
                    }
                    if ($j < $width - 1) {
                        $nextRowColorStorage[$j + 1] = $this->getRGBColorAt($im, $j + 1, $i + 1);
                    }
                }

                foreach ($closestColor as $key => $channel) {
                    $quantError = $color[$key] - $closestColor[$key];
                    if ($j < $width - 1) {
                        $currentRowColorStorage[$j + 1][$key] += $quantError * 7 / 16 * $amount;
                    }
                    if ($i < $height - 1) {
                        if ($j > 0) {
                            $nextRowColorStorage[$j - 1][$key] += $quantError * 3 / 16 * $amount;
                        }
                        $nextRowColorStorage[$j][$key] += $quantError * 5 / 16 * $amount;
                        if ($j < $width - 1) {
                            $nextRowColorStorage[$j + 1][$key] += $quantError * 1 / 16 * $amount;
                        }
                    }
                }

                $newColor = imagecolorallocate($newImage, $closestColor[0], $closestColor[1], $closestColor[2]);
                imagesetpixel($newImage, $j, $i, $newColor);
            }
        }

        return $newImage;
    }

    /**
     * Get the closest available color from a color palette.
     *
     * @param array $pixel   The pixel that contains the color to be calculated
     * @param array $palette The palette that contains all the available colors
     * @param string $mode   The calculation mode, the value is 'rgb' or 'lab', 'rgb' is default value.
     * @return array The closest color from the palette
     */
    private function getClosestColor($pixel, &$palette, $mode = 'rgb')
    {
        $closestColor = null;
        $closestDistance = null;

        foreach ($palette as $color) {
            $distance = $this->calculateEuclideanDistanceSquare($pixel[$mode], $color[$mode]);
            if (isset($closestColor)) {
                if ($distance < $closestDistance) {
                    $closestColor    = $color;
                    $closestDistance = $distance;
                } else if ($distance === $closestDistance) {
                    // nothing need to do
                }
            } else {
                $closestColor    = $color;
                $closestDistance = $distance;
            }
        }

        return $closestColor;
    }

    /**
     * Calculate the square of the euclidean distance of two colors.
     *
     * @param array $p The first color
     * @param array $q The second color
     * @return float The square of the euclidean distance of first color and second color
     */
    private function calculateEuclideanDistanceSquare($p, $q)
    {
        return pow(($q[0] - $p[0]), 2) + pow(($q[1] - $p[1]), 2) + pow(($q[2] - $p[2]), 2);
    }

    /**
     * Calculate the RGB color of a pixel.
     *
     * @param resource $im The image resource
     * @param integer $x        The x-coordinate of the pixel
     * @param integer $y        The y-coordinate of the pixel
     * @return array An array with red, green and blue values of the pixel
     */
    private function getRGBColorAt($im, $x, $y)
    {
        $index = imagecolorat($im, $x, $y);

        return array(($index >> 16) & 0xFF, ($index >> 8) & 0xFF, $index & 0xFF);
    }

    /**
     * Convert an RGB color to a Lab color(CIE Lab).
     *
     * @param array $rgb The RGB color
     * @return array The Lab color
     */
    private function RGBtoLab($rgb)
    {
        return $this->XYZtoCIELab($this->RGBtoXYZ($rgb));
    }

    /**
     * Convert an RGB color to an XYZ space color.
     * observer = 2°, illuminant = D65
     * http://easyrgb.com/index.php?X=MATH&H=02#text2
     *
     * @param array $rgb The RGB color
     * @return array The XYZ space color
     */
    private function RGBtoXYZ($rgb)
    {
        $r = $rgb[0] / 255;
        $g = $rgb[1] / 255;
        $b = $rgb[2] / 255;

        if ($r > 0.04045) {
            $r = pow((($r + 0.055) / 1.055), 2.4);
        } else {
            $r = $r / 12.92;
        }

        if ($g > 0.04045) {
            $g = pow((($g + 0.055) / 1.055), 2.4);
        } else {
            $g = $g / 12.92;
        }

        if ($b > 0.04045) {
            $b = pow((($b + 0.055) / 1.055), 2.4);
        } else {
            $b = $b / 12.92;
        }

        $r *= 100;
        $g *= 100;
        $b *= 100;

        return array(
            $r * 0.4124 + $g * 0.3576 + $b * 0.1805,
            $r * 0.2126 + $g * 0.7152 + $b * 0.0722,
            $r * 0.0193 + $g * 0.1192 + $b * 0.9505
        );
    }

    /**
     * Convert an XYZ space color to a CIE Lab color.
     * observer = 2°, illuminant = D65.
     * http://www.easyrgb.com/index.php?X=MATH&H=07#text7
     *
     * @param array $xyz The XYZ space color
     * @return array The Lab color
     */
    private function XYZtoCIELab($xyz)
    {
        $refX = 95.047;
        $refY = 100;
        $refZ = 108.883;

        $x = $xyz[0] / $refX;
        $y = $xyz[1] / $refY;
        $z = $xyz[2] / $refZ;

        if ($x > 0.008856) {
            $x = pow($x, 1 / 3);
        } else {
            $x = (7.787 * $x) + (16 / 116);
        }

        if ($y > 0.008856) {
            $y = pow($y, 1 / 3);
        } else {
            $y = (7.787 * $y) + (16 / 116);
        }

        if ($z > 0.008856) {
            $z = pow($z, 1 / 3);
        } else {
            $z = (7.787 * $z) + (16 / 116);
        }

        return array(
            (116 * $y) - 16,
            500 * ($x - $y),
            200 * ($y - $z),
        );
    }
}
