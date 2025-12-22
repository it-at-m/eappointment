<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\AppointmentDeleteByCron;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusArchived;
use Psr\Log\LoggerInterface;

class AppointmentDeleteByCronTest extends Base
{
    public function setUp(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->any())
            ->method('info');
        \App::$log = $mockLogger;

        parent::setUp();
    }

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 11:55');
        $helper = new AppointmentDeleteByCron(0, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $helper);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $expired = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(10, $helper->getCount()['preconfirmed']);
    }

    public function testStartProcessingExpiredExakt()
    {
        $now = new \DateTimeImmutable('2016-04-01 07:00');
        $expired = new \DateTimeImmutable('2016-04-01 07:00');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(8, $helper->getCount()['preconfirmed']);
        $this->assertEquals(8, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
     
        $helper->startProcessing(true, false);
        $this->assertEquals(0, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
    }
}
