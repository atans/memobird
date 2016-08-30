<?php

namespace Atans\Memobird\Content;

use Atans\Memobird\Exception;
use Atans\Memobird\Utils\GDIndexedColorConverter;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;

abstract class AbstractPrintContent implements PrintContentInterface
{
    /**
     * @var string
     */
    protected $font;

    /**
     * @var array
     */
    protected $allowedContentTypes = [
        self::TYPE_TEXT,
        self::TYPE_PHOTO
    ];

    /**
     * @var array
     */
    protected $contents = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Fix image of previous page bug
        $this->addText('');
    }


    /**
     * Add text
     *
     * @param  string $text
     * @return PrintContent
     */
    public function addText($text)
    {
        return $this->addContent(self::TYPE_TEXT, $text . "\n");
    }

    /**
     * Add photo
     *
     * @param string $photo
     * @return PrintContent
     */
    public function addPhoto($photo)
    {
        $imagine = new Imagine();
        if (@is_file($photo)) {
            $image = $imagine->open($photo);
        } else {
            $image = $imagine->load($photo);
        }

        // Fix transparent png
        $palette = new RGB();
        $white   = $palette->color('FFF');

        $image = $imagine
            ->create(new Box($image->getSize()->getWidth(), $image->getSize()->getHeight()), $white)
            ->paste($image, new Point(0, 0));

        return $this->addContent(self::TYPE_PHOTO, $image->get('jpg'));
    }

    /**
     * Get print content
     *
     * @return string
     */
    public function getPrintContent()
    {
        return implode(self::CONTENTS_DELIMITER, $this->getContents());
    }

    /**
     * Convert to gbk
     *
     * @param string $string
     * @return string
     */
    public function convert($string)
    {
        return iconv('UTF-8', 'GBK//IGNORE', $string);
    }

    /**
     * Encode string
     *
     * @param string $type
     * @param string $content
     * @return string
     * @throws \Atans\Memobird\Exception\InvalidArgumentException
     */
    public function encode($type, $content)
    {
        switch ($type) {
            case self::TYPE_TEXT:
                $content = $this->convert($content);
                break;
            case self::TYPE_PHOTO:
                $content = $this->imageToBmp($content);
                break;
            default:
                throw new Exception\InvalidArgumentException(sprintf('%s:%s', __METHOD__, 'Error type'));
        }

        return sprintf('%s:%s', $type, base64_encode($content));
    }

    /**
     * Add content
     *
     * @param string $type
     * @param string $content
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function addContent($type, $content)
    {
        if (! in_array($type, $this->allowedContentTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                "%s: Invalid type '%s'",
                __METHOD__,
                $type
            ));
        }

        $content = $this->encode($type, $content);

        $this->contents[] = $content;

        return $this;
    }

    /**
     * Get contents
     *
     * @return array
     */
    public function getContents()
    {
        return $this->contents;
    }


    /**
     * Remove all contents
     *
     * @return $this
     */
    public function removeAll()
    {
        $this->contents = [];
        return $this;
    }

    /**
     * is image only
     *
     * @return bool
     */
    public function isPhotoOnly()
    {
        foreach ($this->getContents() as $content) {
            if (! $this->isPhoto($content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is photo?
     *
     * @param string $content
     * @return bool
     */
    public function isPhoto($content)
    {
        list($type, $content) = explode(self::CONTENT_DELIMITER, $content);
        return self::TYPE_PHOTO == $type;
    }

    /**
     * Is text?
     *
     * @param string $content
     * @return bool
     */
    public function isText($content)
    {
        list($type, $content) = explode(self::CONTENT_DELIMITER, $content);
        return self::TYPE_TEXT == $type;
    }

    /**
     * 转换可打印格式
     *
     * @param string $content
     * @return string
     */
    public function imageToBmp($content)
    {
        $imagine = new Imagine();
        $image = $imagine->load($content);

        // 宽 > 384
        if ($image->getSize()->getHeight() > self::CONTENT_MAX_WIDTH) {
            $image->resize($image->getSize()->widen(self::CONTENT_MAX_WIDTH));
        }

        // 转180度
        $image->rotate(180);
        $image->effects()->grayscale();

        /**
         * @var resource $im
         */
        $im = $image->getGdResource();

        // 水平反转
        imageflip($im, IMG_FLIP_HORIZONTAL);

        // 仿生处理
        $converter = new GDIndexedColorConverter();

        $palette = array(
            array(0, 0, 0),
            array(255, 255, 255),
        );

        /**
         * @var resource $im
         */
        $im = $converter->convertToIndexedColor($im, $palette, 0.8);

        // 转为单色 bmp
        $dWord = function ($n) {
            return pack("V", $n);
        };
        $word  = function ($n) {
            return pack("v", $n);
        };

        $width    = imagesx($im);
        $height   = imagesy($im);
        $widthPad = str_pad('', (4 - ceil($width / 8) % 4) % 4, "\0");

        $size = 62 + (ceil($width / 8) + strlen($widthPad)) * $height;

        $header['identifier']       = 'BM';
        $header['file_size']        = $dWord($size);
        $header['reserved']         = $dWord(0);
        $header['bitmap_data']      = $dWord(62);
        $header['header_size']      = $dWord(40);
        $header['width']            = $dWord($width);
        $header['height']           = $dWord($height);
        $header['planes']           = $word(1);
        $header['bits_per_pixel']   = $word(1);
        $header['compression']      = $dWord(0);
        $header['data_size']        = $dWord(0);
        $header['h_resolution']     = $dWord(0);
        $header['v_resolution']     = $dWord(0);
        $header['colors']           = $dWord(0);
        $header['important_colors'] = $dWord(0);
        $header['white']            = chr(255) . chr(255) . chr(255) . chr(0);
        $header['black']            = chr(0) . chr(0) . chr(0) . chr(0);

        $bmp = '';
        foreach ($header AS $h) {
            $bmp .= $h;
        }

        $str = '';
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($im, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                $g   = ($rgb >> 8) & 0xFF;
                $b   = $rgb & 0xFF;
                $gs  = (($r * 0.299) + ($g * 0.587) + ($b * 0.114));
                if ($gs > 150) $color = 0;
                else $color = 1;
                $str = $str . $color;
                if ($x == $width - 1) {
                    $str = str_pad($str, 8, "0");
                }
                if (strlen($str) == 8) {
                    $bmp .= chr((int)bindec($str));
                    $str = "";
                }
            }
            $bmp .= $widthPad;
        }

        imagedestroy($im);

        return $bmp;
    }

    /**
     * Get font
     *
     * @return string
     */
    public function getFont()
    {
        if (! $this->font) {
            $this->setFont(__DIR__ . '/../resources/font/msyh.ttf');
        }
        return $this->font;
    }

    /**
     * Set font
     *
     * @param  string $font
     * @return $this
     */
    public function setFont($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPrintContent();
    }
}