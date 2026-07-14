<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Process;

class WorkstationProcessRemoveTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationProcessRemove";

    const PROCESS_ID = 10030;

    const AUTHKEY = '1c56';

    const CALLED_PROCESS_ID = 11468;

    const CALLED_AUTHKEY = '7b41';

    public function testRequeueAndSkipToNextKeepsProcessOnWaitingListWithinCallCountMax()
    {
        $this->setWorkstationWithCallCountMax('1');
        $this->assignCalledProcessToWorkstation(1);

        $response = $this->render([], ['action' => 'requeue_and_skip_to_next'], []);
        $this->assertEquals(200, $response->getStatusCode());

        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $this->assertNotEquals(\BO\Zmsentities\Process::STATUS_MISSED, $process->status);
        $this->assertFalse($process->getWasMissed());
    }

    public function testRequeueAndSkipToNextMarksProcessMissedWhenCallCountExceedsMax()
    {
        $this->setWorkstationWithCallCountMax('1');
        $this->assignCalledProcessToWorkstation(2);

        $response = $this->render([], ['action' => 'requeue_and_skip_to_next'], []);
        $this->assertEquals(200, $response->getStatusCode());

        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $this->assertEquals(\BO\Zmsentities\Process::STATUS_MISSED, $process->status);
        $this->assertTrue($process->getWasMissed());
    }

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->process = (new \BO\Zmsentities\Process())->getExample();
        User::$workstation->process->id = self::PROCESS_ID;
        User::$workstation->process->authKey = self::AUTHKEY;
        $response = $this->render([], [], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"process"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [], []);
    }

    private function setWorkstationWithCallCountMax(string $callCountMax): void
    {
        $this->setWorkstation();
        User::$workstation->scope['preferences']['queue']['callCountMax'] = $callCountMax;
    }

    private function assignCalledProcessToWorkstation(int $callCount): void
    {
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $previousStatus = $process->status;
        $nowTs = \App::$now->getTimestamp();

        $process->status = \BO\Zmsentities\Process::STATUS_CALLED;
        $process['status'] = \BO\Zmsentities\Process::STATUS_CALLED;
        $process->queue['callCount'] = $callCount;
        $process->queue['callTime'] = $nowTs;
        $process->queue['lastCallTime'] = $nowTs;
        $process->queue['arrivalTime'] = $nowTs - 600;
        $process->queue['waitingTime'] = '00:10:00';

        $process = (new \BO\Zmsbackend\Process\Service\Process())->updateEntity($process, \App::$now, 0, $previousStatus);
        User::$workstation->process = (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeAssignedProcess(
            User::$workstation,
            $process,
            \App::$now
        );
    }
}
