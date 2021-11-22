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
        $processList = $query->readNotificationReminderProcessList($now, 10, 2);
        $this->assertEquals(10, $processList->count());
        $this->assertEquals(
            '2016-04-01 07:45',
            $processList->getFirst()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertTrue($processList->getFirst()->getFirstClient()->hasTelephone());
        $this->assertEquals(
            '2016-04-01 12:45',
            $processList->getLast()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertTrue($processList->getLast()->getFirstClient()->hasTelephone());
    }
}
