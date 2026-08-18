<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\ProcessSearchHistory\Service\ProcessSearchHistory as HistoryService;

class ProcessFinishedTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessFinished";

    public function testRendering()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation->getUseraccount()->setPermissions('appointment');
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process->status = 'finished';
        $process->clients[0]->telephone = '08912345678';
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertStringContainsString('"status":"finished"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity($process->id, new \BO\Zmsbackend\Helper\NoAuth);
        $this->assertEquals('blocked', $entity->status);

        $historyEntry = $this->readHistoryEntry((int) $process->id);

        $this->assertIsArray($historyEntry);
        $this->assertSame((int) $process->id, (int) $historyEntry['process_id']);
        $this->assertSame(HistoryService::STATUS_COMPLETED, $historyEntry['status']);
        $this->assertSame(\App::$now->format('Y-m-d H:i:s'), $historyEntry['finalized_at']);
        $this->assertSame('08912345678', $historyEntry['telephone']);

        return $response;
    }

    public function testRenderingPending()
    {
        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation->getUseraccount()->setPermissions('appointment');
        $workstation['queue']['clusterEnabled'] = 1;

        $process = json_decode($this->readFixture("GetProcess_10068.json"));
        $process->status = 'pending';
        $response = $this->render([], [
            '__body' => json_encode($process)
        ], []);

        $this->assertStringContainsString('"status":"pending"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());

        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity($process->id, new \BO\Zmsbackend\Helper\NoAuth);
        $this->assertEquals('pending', $entity->status);

        $this->assertSame(0, $this->countHistoryEntries((int) $process->id));
    }

    public function testUnvalidCredentials()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessInvalid');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => $this->readFixture("GetProcess_10030.json")
        ], []);
    }

    public function testNoAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);

        $workstation = $this->setWorkstation(138, 'berlinonline', 141);
        $workstation->getUseraccount()->setPermissions('appointment');
        $workstation['queue']['clusterEnabled'] = 1;
        $workstation->process = json_decode($this->readFixture("GetProcess_10030.json"));
        $process = json_decode($this->readFixture("GetProcess_10029.json"));
        $process->status = 'finished';
        $this->render([], [
            '__body' => json_encode($process)
        ], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "unvalid"
            }'
        ], []);
    }

    public function testProcessNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "abcd",
                "status": "finished",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": 10029,
                "authKey": "abcd",
                "status": "finished",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    private function readHistoryEntry(int $processId)
    {
        return (new HistoryService())->fetchRow(
            '
                SELECT
                    `process_id`,
                    `status`,
                    `finalized_at`,
                    `telephone`
                FROM `process_search_history`
                WHERE `process_id` = :processId
                ORDER BY `id` DESC
                LIMIT 1
            ',
            [
                'processId' => $processId,
            ]
        );
    }

    private function countHistoryEntries(int $processId): int
    {
        return (int) (new HistoryService())->fetchValue(
            '
                SELECT COUNT(*)
                FROM `process_search_history`
                WHERE `process_id` = :processId
            ',
            [
                'processId' => $processId,
            ]
        );
    }
}
