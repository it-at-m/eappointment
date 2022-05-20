<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessMailReminderTest extends Base
{
    public function testSendMailReminder()
    {
        $query = new Query();
        $now = new \DateTimeImmutable("2016-04-01 13:40");
        $lastRun = new \DateTimeImmutable("2016-04-01 13:35");
        $processList = $query->readEmailReminderProcessListByInterval($now, $lastRun, 7200, 10, null, 2);

        $this->assertEquals(7, $processList->count());
        $this->assertEquals(
            '2016-04-01 13:50',
            $processList->getFirst()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertEquals(
            '2016-04-01 13:50',
            $processList->getLast()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
    }

    public function testCronHelper()
    {
        $now = new \DateTimeImmutable("2016-04-01 13:40");
        $helper = new \BO\Zmsdb\Helper\SendMailReminder($now, 2, false);
        $helper->setLimit(6);
        $helper->setLoopCount(3);
        $helper->startProcessing(true);
        $this->assertEquals(6, $helper->getCount());
    }
}
