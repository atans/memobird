<?php
namespace MemobirdTest\Result;

use Atans\Memobird\Result\PrintPaperResult;
use Atans\Memobird\Result\PrintStatusResult;
use Atans\Memobird\Result\UserBindResult;

class PrintContentTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $userBindResult = new UserBindResult();
        $this->assertInstanceOf('Atans\Memobird\Result\ResultInterface', $userBindResult);
        $this->assertInstanceOf('Atans\Memobird\Result\AbstractResult', $userBindResult);

        $printPaperResult = new PrintPaperResult();
        $this->assertInstanceOf('Atans\Memobird\Result\ResultInterface', $printPaperResult);
        $this->assertInstanceOf('Atans\Memobird\Result\AbstractResult', $printPaperResult);

        $printStatusResult = new PrintStatusResult();
        $this->assertInstanceOf('Atans\Memobird\Result\ResultInterface', $printStatusResult);
        $this->assertInstanceOf('Atans\Memobird\Result\AbstractResult', $printStatusResult);
    }
}
