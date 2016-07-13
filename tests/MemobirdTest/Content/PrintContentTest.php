<?php
namespace MemobirdTest\Content;

use Atans\Memobird\Content\PrintContent;

class PrintContentTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $printContent = new PrintContent();
        $this->assertInstanceOf('Atans\Memobird\Content\PrintContentInterface', $printContent);
        $this->assertInstanceOf('Atans\Memobird\Content\AbstractPrintContent', $printContent);
    }

    public function testText()
    {
        $printContent = new PrintContent();
        $text1 = 'Hello World';
        $printContent->addText($text1);

        $expected1 = 'T:' . $this->convertToGbk($text1 . "\n");
        $this->assertEquals($expected1, $printContent->getPrintContent());

        $text2 = 'Another text';
        $printContent->addText($text2);

        $expected2 = $expected1 . '|T:' . $this->convertToGbk($text2 . "\n");
        $this->assertEquals($expected2, $printContent->getPrintContent());

    }

    public function testTextImageAutoWrap()
    {
        $printContent = new PrintContent();
        $printContent->textToImage('在观看夏季联赛期间，奇才当家控卫约翰-沃尔应邀来到解说席。不仅解说了比赛，还讨论了今年休赛期的热门话题。当被问到如何看待杜兰特转会一事，沃尔显得非常理解：“呃，首先我想说的是，现...')
            ->save(__DIR__ . '/../images/text-image-auto-wrap.jpg');
    }

    public function testTextImage()
    {
        $printContent = new PrintContent();

        $printContent->textToImage(str_repeat('你好,', 4), array(
            'align' => 'center',
            'size' => 20,
        ))->save(__DIR__ . '/../images/text-image-centered-text.jpg');

        $printContent->textToImage(str_repeat('你好,', 4), array(
            'align' => 'center',
            'size' => 20,
            'vertical' => true,
        ))->save(__DIR__ . '/../images/text-image-vertical-text.jpg');
    }

        /**
     * @expectedException \Atans\Memobird\Exception\InvalidArgumentException
     */
    public function testException()
    {
        $printContent = new PrintContent();
        $printContent->addContent('UNKNOWN', 'test error type');
    }

    public function convertToGbk($string)
    {
        return $this->encode(iconv('UTF-8', 'GBK//IGNORE', $string));
    }

    public function encode($string)
    {
        return base64_encode($string);
    }
}
