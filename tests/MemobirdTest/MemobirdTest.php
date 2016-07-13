<?php

namespace MemobirdTest;

use Atans\Memobird\Content\PrintContent;
use Atans\Memobird\Memobird;

class MemobirdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Memobird
     */
    protected $memobird;

    /**
     * @var array
     */
    protected $config;

    public function setUp()
    {
        $this->config = $GLOBALS['config'];
        if (empty($this->config['ak'])) {
            echo 'Please set ak';
        }
        
        $this->memobird = new Memobird($this->config['ak']);
        parent::setUp();
    }

    public function testAk()
    {
        $this->assertEquals($this->config['ak'], $this->memobird->getAk());
    }

    public function testCache()
    {
        $cache = $this->memobird->getCache();

        $key = 'test';
        $value = 'This is a value';

        $cache->save($key, $value);

        $this->assertEquals($value, $cache->fetch($key));
    }

    public function testLogger()
    {
        $logger = $this->memobird->getLogger();

        $logger->info('test');

        $logFile = __DIR__ . '/.././../memobird.log';

        $logExists = file_exists($logFile);

        $this->assertEquals(true, $logExists, 'Log file created ?');

        if ($logExists) {
            $logger->popHandler();
            unlink($logFile);
        }
    }

    public function testCacheKeyPrefix()
    {
        $this->assertEquals('', $this->memobird->getCacheKeyPrefix(), 'Test default value');

        $prefix = 'test';
        $this->memobird->setCacheKeyPrefix($prefix);
        $this->assertEquals($prefix, $this->memobird->getCacheKeyPrefix());

        $memobirdId = '123456789';
        $this->assertEquals($prefix . $memobirdId, $this->memobird->getCacheKey($memobirdId));
    }

    public function testUserBind()
    {
        $userBindResult = $this->memobird->userBind($this->config['memobird_id']);

        $this->assertTrue($userBindResult->success());
        $this->assertGreaterThan(0, $userBindResult->getShowapiUserid(), 'User id > 0');
        $this->assertEquals(1, $userBindResult->getShowapiResCode(), 'Res code = 1');
        $this->assertEquals('ok', $userBindResult->getShowapiResError(), 'Res error = ok');
    }

    public function testUserBindFailure()
    {
        $userBindResult = $this->memobird->userBind(12345);

        $this->assertFalse($userBindResult->success());
        $this->assertEquals(0, $userBindResult->getShowapiUserid(), 'User id = 0');
        $this->assertEquals(2, $userBindResult->getShowapiResCode(), 'Res code = 2');
        $this->assertEquals('咕咕机未激活或者未绑定', $userBindResult->getShowapiResError(), 'Res error = ok');
    }

    public function testUserIdCache()
    {
        $this->memobird->getUserIdByMemobirdId($this->config['memobird_id']);
        $userId = $this->memobird->getUserIdByMemobirdId($this->config['memobird_id']);

        $this->assertEquals($this->memobird->userBind($this->config['memobird_id'])->getShowapiUserid(), $userId, 'User cache test');
    }

    /**
     * @expectedException \Atans\Memobird\Exception\UserIdNotFoundException
     */
    public function testUserIdFailure()
    {
        $this->memobird->getUserIdByMemobirdId(123456);
    }

    public function testPrintTextAndPrintStatus()
    {
        $printContent = new PrintContent();
        $printContent->addText('Hello World');

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');

        $printStatusResult = $this->memobird->getPrintStatus($printPaperResult->getPrintcontentid());

        $this->assertTrue($printStatusResult->success());
        $this->assertEquals(1, $printStatusResult->getShowapiResCode(), 'Res code = 1');
        $this->assertEquals($printPaperResult->getPrintcontentid(), $printStatusResult->getPrintcontentid());
        $this->assertEquals('ok', $printStatusResult->getShowapiResError(), 'Res error = ok');
        $this->assertGreaterThanOrEqual(0, $printStatusResult->getPrintflag(), 'Print flag = 1');
    }

    public function testPrintTextImage()
    {
        $printContent = new PrintContent();
        $printContent->addTextImage('Hello World', array('size' => 20))
            ->addTextImage('你好，世界', array('size' => 20));

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');
    }

    public function testAddTextAndPhoto()
    {
        $printContent = new PrintContent();
        $printContent->addText('Hello World, 你好世界')
            ->addTextImage('Hello World, 你好世界')
            ->addTextImage('繁體中文')
            ->addPhoto(__DIR__ . '/images/logo.png');

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');
    }

    public function testTextImageAutoWrap()
    {
        $printContent = new PrintContent();
        $printContent->addText('京东配送员【颜昌友】已出发，联系电话【15919679718，感谢您的耐心等待，参加评价还能赢取京豆呦')
            ->addText('在观看夏季联赛期间，奇才当家控卫约翰-沃尔应邀来到解说席。不仅解说了比赛，还讨论了今年休赛期的热门话题。当被问到如何看待杜兰特转会一事，沃尔显得非常理解：“呃，首先我想说的是，现...');

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');
    }

    public function testPrintedTime()
    {
        $printContent = new PrintContent();
        $printContent->addPrintedTime();

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');
    }

    public function testAddTextImageAndLineAndQrCode()
    {
        $printContent = new PrintContent();
        $printContent->addTextImage('QR CODE')
            ->addLine()
            ->addQrCode('http://memobird.cn');

        $printPaperResult = $this->memobird->printPaper($this->config['memobird_id'], $printContent);

        $this->assertTrue($printPaperResult->success());
        $this->assertEquals(1, $printPaperResult->getShowapiResCode(), 'Res code = 1');
        $this->assertGreaterThanOrEqual(1, $printPaperResult->getResult(), 'Result = 1 or 2');
        $this->assertGreaterThan(0, $printPaperResult->getPrintcontentid(), 'Printcontentid > 0');
    }
}
