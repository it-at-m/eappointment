<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\QueueStatusPermission;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\QueueList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Queue;
use BO\Zmsentities\Useraccount;
use PHPUnit\Framework\TestCase;

class QueueStatusPermissionTest extends TestCase
{
    public function testAppointmentAloneDoesNotAllowWaitingStatuses(): void
    {
        $user = (new Useraccount())->setPermissions('appointment');

        $this->assertFalse(QueueStatusPermission::isStatusAllowed($user, 'confirmed'));
        $this->assertFalse(QueueStatusPermission::isStatusAllowed($user, 'queued'));
    }

    public function testWaitingqueueAllowsWaitingStatuses(): void
    {
        $user = (new Useraccount())->setPermissions('waitingqueue');

        $this->assertTrue(QueueStatusPermission::isStatusAllowed($user, 'confirmed'));
        $this->assertTrue(QueueStatusPermission::isStatusAllowed($user, 'queued'));
    }

    public function testParkedStatusRequiresParkedqueue(): void
    {
        $user = (new Useraccount())->setPermissions('appointment');

        $this->assertFalse(QueueStatusPermission::isStatusAllowed($user, 'parked'));

        $user->setPermissions('parkedqueue');
        $this->assertTrue(QueueStatusPermission::isStatusAllowed($user, 'parked'));
    }

    public function testFilterProcessListDropsUnauthorizedStatuses(): void
    {
        $user = (new Useraccount())->setPermissions('parkedqueue', 'missedqueue');

        $list = new ProcessList();
        $list->addEntity(new Process(['id' => 1, 'status' => 'confirmed']));
        $list->addEntity(new Process(['id' => 2, 'status' => 'parked']));
        $list->addEntity(new Process(['id' => 3, 'status' => 'missed']));
        $list->addEntity(new Process(['id' => 4, 'status' => 'finished']));

        $filtered = QueueStatusPermission::filterProcessList($list, $user);

        $this->assertCount(2, $filtered);
        $statuses = [];
        foreach ($filtered as $process) {
            $statuses[] = $process->getStatus();
        }
        $this->assertSame(['parked', 'missed'], $statuses);
    }

    public function testFilterQueueListDropsUnauthorizedStatuses(): void
    {
        $user = (new Useraccount())->setPermissions('finishedqueue');

        $list = new QueueList();
        $list->addEntity(new Queue(['arrivalTime' => 1, 'status' => 'queued']));
        $list->addEntity(new Queue(['arrivalTime' => 2, 'status' => 'finished']));

        $filtered = QueueStatusPermission::filterQueueList($list, $user);

        $this->assertCount(1, $filtered);
        $this->assertSame('finished', $filtered->getFirst()->status);
    }
}
