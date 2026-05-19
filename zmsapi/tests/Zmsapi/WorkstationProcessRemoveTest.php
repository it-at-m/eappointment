<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Process;

class WorkstationProcessRemoveTest extends Base
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

        $process = (new \BO\Zmsdb\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $this->assertNotEquals(Process::STATUS_MISSED, $process->status);
        $this->assertFalse($process->getWasMissed());
    }

    public function testRequeueAndSkipToNextMarksProcessMissedWhenCallCountExceedsMax()
    {
        $this->setWorkstationWithCallCountMax('1');
        $this->assignCalledProcessToWorkstation(2);

        $response = $this->render([], ['action' => 'requeue_and_skip_to_next'], []);
        $this->assertEquals(200, $response->getStatusCode());

        $process = (new \BO\Zmsdb\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $this->assertEquals(Process::STATUS_MISSED, $process->status);
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
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
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
        $process = (new \BO\Zmsdb\Process())->readEntity(self::CALLED_PROCESS_ID, self::CALLED_AUTHKEY, 1);
        $process->status = Process::STATUS_CALLED;
        $process['status'] = Process::STATUS_CALLED;
        $process->queue['callCount'] = $callCount;
        $process = (new \BO\Zmsdb\Process())->updateEntity($process, \App::$now, 0, Process::STATUS_CONFIRMED);
        User::$workstation->process = (new \BO\Zmsdb\Workstation())->writeAssignedProcess(
            User::$workstation,
            $process,
            \App::$now
        );
    }
}
