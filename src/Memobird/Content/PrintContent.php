<?php

namespace Atans\Memobird\Content;

use Endroid\QrCode\QrCode;
use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;

class PrintContent extends AbstractPrintContent
{
    const ALIGN_LEFT   = 'left';
    const ALIGN_CENTER = 'center';
    const ALIGN_RIGHT  = 'right';

    /**
     * Add line
     *
     * @param array $options
     * @return PrintContent
     */
    public function addLine(array $options = [])
    {
        $options = array_merge([
            'thickness'      => 3,
            'padding_top'    => 10,
            'padding_bottom' => 10,
            'padding_left'   => 0,
            'padding_right'  => 0,
        ], $options);

        $imagine      = new Imagine();
        $canvasHeight = $options['padding_top'] + $options['thickness'] + $options['padding_bottom'];
        $canvas       = $imagine->create(new Box(self::CONTENT_MAX_WIDTH, $canvasHeight));

        $palette = new RGB();

        $canvas->draw()->line(
            new Point($options['padding_left'], $options['padding_top']),
            new Point(self::CONTENT_MAX_WIDTH - $options['padding_left'], $options['padding_top']),
            $palette->color('000'),
            $options['thickness']
        );

        return $this->addPhoto($canvas->get('jpg'));
    }

    /**
     * Add qr code
     *
     * @param string $text
     * @param array $options
     * @return PrintContent
     * @throws \Endroid\QrCode\Exceptions\DataDoesntExistsException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionFailedException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     */
    public function addQrCode($text, array $options = [])
    {
        $options = array_merge([
            'size'      => null,
            'padding'   => null,
            'logo'      => null,
            'logo_size' => null,
        ], $options);

        $qrCode = new QrCode();

        $qrCode->setText($text);

        if (is_numeric($options['size'])) {
            $qrCode->setSize($options['size']);
        }

        if (is_numeric($options['padding'])) {
            $qrCode->setPadding($options['padding']);
        }

        if ($options['logo'] && file_exists($options['logo'])) {
            $qrCode->setLogo($options['logo']);

            if (is_numeric($options['logo_size'])) {
                $qrCode->setLogoSize($options['logo_size']);
            }
        }

        return $this->addPhoto($qrCode->get('jpg'));
    }

    /**
     * Add text image
     *
     * @param string $text
     * @param array $options
     * @return $this
     */
    public function addTextImage($text, array $options = array())
    {
        return $this->addPhoto($this->textToImage($text, $options)->get('jpg'));
    }

    /**
     * Text to image
     *
     * @param string $text
     * @param array $options
     * @return \Imagine\Gd\Image|\Imagine\Image\ImageInterface
     */
    public function textToImage($text, array $options = array())
    {
        $options = array_merge([
            'align'          => self::ALIGN_LEFT,
            'size'           => 20,
            'font'           => null,
            'vertical'       => false,
            'padding_top'    => null,
            'padding_bottom' => null,
            'padding_left'   => null,
            'padding_right'  => null,
            'line_height'    => null,
        ], $options);

        $imagine = new Imagine();
        $palette = new RGB;
        $white   = $palette->color('FFF');
        $black   = $palette->color('000');

        $fontFile = $options['font'] ? $options['font'] : $this->getFont();
        $font     = new Font($fontFile, $options['size'], $black);

        $textBox       = $font->box('我');
        $textWidth     = $textBox->getWidth();
        $textHeight    = $textBox->getHeight();
        $paddingTop    = $options['padding_top'] ? $options['padding_top'] : $textHeight / 2;
        $paddingBottom = $options['padding_bottom'] ? $options['padding_bottom'] : $textHeight / 2;
        $paddingLeft   = $options['padding_bottom'] ? $options['padding_bottom'] : $textWidth / 2;
        $paddingRight  = $options['padding_bottom'] ? $options['padding_bottom'] : $textWidth / 2;

        if ($options['vertical']) {
            $textBox      = $font->box($text);
            $canvasWidth  = $textBox->getWidth() + $paddingLeft + $paddingRight;
            $canvasHeight = $textBox->getHeight() + $paddingTop + $paddingBottom;
            $textCanvas   = $imagine->create(new Box($canvasWidth, $canvasHeight), $white);
            $textCanvas->draw()->text($text, $font, new Point($paddingLeft, $paddingTop));
            $textCanvas->rotate(90);

            $newHeight = $textCanvas->getSize()->getHeight();
            $canvas    = $imagine->create(new Box(self::CONTENT_MAX_WIDTH, $newHeight));
            $canvas->paste($textCanvas, new Point((self::CONTENT_MAX_WIDTH - $textCanvas->getSize()->getWidth()) / 2, 0));
        } else {
            $maxWidth = self::CONTENT_MAX_WIDTH - $paddingLeft - $paddingRight;

            $lines     = $this->stringToMultipleLines($text, $font, $maxWidth);
            $lineCount = count($lines);

            $lineHeight = $options['line_height'] ? $options['line_height'] : $textHeight / 2;

            $canvasHeight = $paddingTop + $paddingBottom + ($textHeight * $lineCount) + ($lineHeight * ($lineCount - 1));
            $canvas       = $imagine->create(new Box(self::CONTENT_MAX_WIDTH, $canvasHeight));
            $y            = $paddingTop;

            foreach ($lines as $index => $line) {
                try {
                    $width = $font->box($line)->getWidth();
                } catch (\Imagine\Exception\InvalidArgumentException $e) {
                    $width = $textBox->getWidth();
                }

                switch ($options['align']) {
                    case self::ALIGN_RIGHT:
                        $x = $maxWidth - $width - $paddingLeft;
                        break;
                    case self::ALIGN_CENTER:
                        $x = (self::CONTENT_MAX_WIDTH - $width) / 2;
                        break;
                    default:
                        $x = $paddingLeft;
                        break;
                }

                $x = $x < 0 ? 0 : $x;

                $point = new Point($x, $y);

                $canvas->draw()->text($line, $font, $point);

                if ($index + 1 < $lineCount) {
                    $y += $textHeight + $lineHeight;
                }
            }
        }

        return $canvas;
    }

    /**
     * String to multiple lines
     *
     * @param string $string
     * @param Font $font
     * @param int $maxWidth
     * @return array
     */
    public function stringToMultipleLines($string, Font $font, $maxWidth)
    {
        $fontBox  = $font->box('我');
        $maxWidth = $maxWidth - $fontBox->getWidth();

        preg_match_all('/./u', $string, $words);
        $words = $words[0];
        $lines = [];

        $width  = 0;
        $offset = 0;
        foreach ($words as $word) {

            if ($this->isSbcCaseCharacter($word)) {
                $wordWidth = $fontBox->getWidth();
            } else {
                try {
                    $wordWidth = $font->box($word)->getWidth();
                } catch (\Imagine\Exception\InvalidArgumentException $e) {
                    $wordWidth = $fontBox->getWidth();
                }
            }

            if (($width + $wordWidth) > $maxWidth) {
                $width = $wordWidth;
                $offset++;
            } else {
                $width += $wordWidth;
            }

            if (! isset($lines[$offset])) {
                $lines[$offset] = '';
            }

            $lines[$offset] .= $word;
        }

        return $lines;
    }

    /**
     * Is sbc case character
     *
     * @param $string
     * @return bool
     */
    public function isSbcCaseCharacter($string)
    {
        return in_array($string, [
            '０' , '１' , '２' , '３' , '４' , '５' , '６' , '７' , '８' , '９' ,
            'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' , 'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
            'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' , 'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
            'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' , 'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
            'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' , 'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
            'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' , 'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
            'ｙ' , 'ｚ' , '－' , '　' , '：' , '．' , '，' , '／' , '％' , '＃' ,
            '！' , '＠' , '＆' , '（' , '）' , '＜' , '＞' , '＂' , '＇' , '？' ,
            '［' , '］' , '｛' , '｝' , '＼' , '｜' , '＋' , '＝' , '＿' , '＾' ,
            '￥' , '￣' , '｀']);
    }

    /**
     * Add print time
     *
     * @param null|string $prefix
     * @param null|string $format
     * @param array $options
     * @return $this
     */
    public function addPrintedTime($prefix = null, $format = null, $options =[])
    {
        if (empty($prefix)) {
            $prefix = 'Printed at: ';
        }

        if (empty($format)) {
            $format = 'Y-m-d H:i:s';
        }
        
        $this->addTextImage(sprintf('%s%s', $prefix, date($format)), array_merge([
            'size' => '12',
        ], $options));

        return $this;
    }

}