<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessNotificationTest extends Base
{
    public function testSendNotificationReminder()
    {
        $query = new Query();
        $now = static::$now;
        $processList = $query->readNotificationReminderProcessList($now, 15, null, 2);
        $this->assertEquals(14, $processList->count());
        $this->assertEquals(
            '2016-04-01 07:45',
            $processList->getFirst()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertTrue($processList->getFirst()->getFirstClient()->hasTelephone());
        $this->assertEquals(
            '2016-04-01 12:30',
            $processList->getLast()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertTrue($processList->getLast()->getFirstClient()->hasTelephone());
    }

    public function testCronHelper()
    {
        $now = static::$now;
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->startProcessing(true);
        $this->assertEquals(14, $helper->getCount());
    }

    public function testCronHelperWithLoopLimit()
    {
        $now = static::$now;
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(true);
        $this->assertEquals(10, $helper->getCount());
    }
}
