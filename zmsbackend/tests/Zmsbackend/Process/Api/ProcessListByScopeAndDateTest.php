<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessListByScopeAndDateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByScopeAndDate";

    private function setWorkstationWithScopeAccess($scopeId = 141): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment', 'waitingqueue', 'parkedqueue', 'missedqueue', 'finishedqueue', 'openqueue');

        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity(new \BO\Zmsentities\Scope(['id' => $scopeId]));

        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => $scopeList,
        ]));
    }

    public function testRendering()
    {
        $this->setWorkstationWithScopeAccess();
        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGraphQL()
    {
        $this->setWorkstationWithScopeAccess();
        $response = $this->render(
            ['id' => 141, 'date' => '2016-04-01'],
            ['gql' => '{ id authKey scope{ id source shortName } }', 'resolveReferences' => 1],
            []
        );
        $this->assertStringContainsString('$schema', (string)$response->getBody());
        $this->assertStringContainsString('"id":141,"source":"dldb"', (string)$response->getBody());
        $this->assertStringNotContainsString('"provider"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstationWithScopeAccess();
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'date' => '2016-04-01'], [], []);
    }

    public function testWithResolveReferencesZero()
    {
        $this->setWorkstationWithScopeAccess();
        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], ['resolveReferences' => 0]);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProviderSlotTimeIsPresentInProcessScope()
    {
        $this->setWorkstationWithScopeAccess();
        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], []);
        $payload = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($payload['data']);
        $this->assertNotEmpty($payload['data']);

        $firstProcess = reset($payload['data']);
        $this->assertArrayHasKey('scope', $firstProcess);
        $this->assertArrayHasKey('provider', $firstProcess['scope']);
        $this->assertArrayHasKey('data', $firstProcess['scope']['provider']);
        $this->assertArrayHasKey('slotTimeInMinutes', $firstProcess['scope']['provider']['data']);
        $this->assertEquals(12, (int) $firstProcess['scope']['provider']['data']['slotTimeInMinutes']);
    }

    public function testStrictQueuePermissionsHidesWaitingWithoutWaitingqueue(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment', 'parkedqueue', 'missedqueue', 'finishedqueue');

        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity(new \BO\Zmsentities\Scope(['id' => 141]));
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => $scopeList,
        ]));

        $response = $this->render(
            ['id' => 141, 'date' => '2016-04-01'],
            ['strictQueuePermissions' => 1],
            []
        );
        $payload = json_decode((string)$response->getBody(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload['data']);

        foreach ($payload['data'] as $process) {
            $this->assertNotContains(
                $process['status'],
                ['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted'],
                'Waiting-pipeline statuses must be hidden without waitingqueue in strict mode'
            );
        }
    }

    public function testAppointmentFallbackKeepsWaitingWithoutWaitingqueue(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');

        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity(new \BO\Zmsentities\Scope(['id' => 141]));
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => $scopeList,
        ]));

        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], []);
        $payload = json_decode((string)$response->getBody(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload['data']);
        $this->assertNotEmpty($payload['data']);

        $hasWaitingStatus = false;
        foreach ($payload['data'] as $process) {
            if (in_array($process['status'], ['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted'], true)) {
                $hasWaitingStatus = true;
                break;
            }
        }
        $this->assertTrue(
            $hasWaitingStatus,
            'Default mode should keep waiting-pipeline statuses for appointment holders'
        );
    }
}
