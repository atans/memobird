<?php
namespace Atans\Memobird\Tests;

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

        $expected1 = 'T:' . $this->convertToGbk($text1);
        $this->assertEquals($expected1, $printContent->getPrintContent());

        $text2 = 'Another text';
        $printContent->addText($text2);

        $expected2 = $expected1 . '|T:' . $this->convertToGbk($text2);
        $this->assertEquals($expected2, $printContent->getPrintContent());

    }

    public function testTextImage()
    {
        $printContent = new PrintContent();

        $printContent->textToImage(str_repeat('你好,', 4), array(
            'align' => 'center',
            'size' => 20,
        ))->save(__DIR__ . '/../images/centered-text.jpg');

        $printContent->textToImage(str_repeat('你好,', 4), array(
            'align' => 'center',
            'size' => 20,
            'vertical' => true,
        ))->save(__DIR__ . '/../images/vertical-text.jpg');
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
