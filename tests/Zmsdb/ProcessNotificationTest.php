<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

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
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $processList = $query->readNotificationReminderProcessList($now, 10, 2);
        $this->assertEquals(10, $processList->count());
        $this->assertEquals(
            '2016-04-01 07:45',
            $processList->getFirst()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertEquals(
            '2016-04-01 11:15',
            $processList->getLast()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
    }
}
