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
        $now = new \DateTimeImmutable('2016-04-07 08:01:00');
        $processList = $query->readNotificationReminderProcessList($now, 10, null, 2);

        $this->assertEquals(0, $processList->count());
        /*
        // TODO: adjust value when writing test for keycloak
        $this->assertEquals(
            '2016-04-08 08:00',
            $processList->getFirst()->getFirstAppointment()->toDateTime()->format('Y-m-d H:i')
        );
        $this->assertEquals(
            '2016-04-07 08:00',
            $now->setTimestamp($processList->getFirst()->reminderTimestamp)->format('Y-m-d H:i')
        );
        $this->assertTrue($processList->getFirst()->getFirstClient()->hasTelephone());
        */
    }

    public function testCronExakt()
    {
        $now = new \DateTimeImmutable('2016-04-07 08:00:00');
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->startProcessing(true);
        // TODO: adjust this value when writing test for openid
        $this->assertEquals(0, $helper->getCount());
    }

    public function testCronWithDelay()
    {
        $now = new \DateTimeImmutable('2016-04-07 08:01:50');
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->startProcessing(true);
        // TODO: adjust this value when writing test for openid
        $this->assertEquals(0, $helper->getCount());
    }

    public function testCronLate()
    {
        $now = new \DateTimeImmutable('2016-04-07 08:05:01');
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->startProcessing(true);
        $this->assertEquals(0, $helper->getCount());
    }

    public function testCronBefore()
    {
        $now = new \DateTimeImmutable('2016-04-07 07:59:50');
        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now, false);
        $helper->startProcessing(true);
        $this->assertEquals(0, $helper->getCount());
    }

    public function testCronWithLimit()
    {
        $now = new \DateTimeImmutable('2016-04-08 08:00:00');
        $query = new \BO\Zmsdb\ProcessStatusFree();
        $input = (new \BO\Zmsdb\Tests\ProcessTest())->getTestProcessEntity();
        
        $process = $query->writeEntityReserved($input, $now);
        $process->reminderTimestamp = ($now->modify('-30 minutes'))->getTimestamp();
        $process = $query->updateEntity($process, $now);
        $process = $query->updateProcessStatus($process, 'confirmed', $now);

        $process2 = $query->writeEntityReserved($input, $now);
        $process2->reminderTimestamp = ($now->modify('-30 minutes'))->getTimestamp();
        $process2 = $query->updateEntity($process2, $now);
        $process2 = $query->updateProcessStatus($process2, 'confirmed', $now);

        $helper = new \BO\Zmsdb\Helper\SendNotificationReminder($now->modify('-30 minutes'), false);
        $helper->setLimit(1);
        $helper->setLoopCount(1);
        $helper->startProcessing(true);
        // TODO: re-use when testing new status preconfirmed
        $this->assertEquals(0, $helper->getCount());
    }
}
