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

}
