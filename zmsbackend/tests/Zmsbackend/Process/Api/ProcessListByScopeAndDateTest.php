<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessListByScopeAndDateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByScopeAndDate";

    private function setWorkstationWithScopeAccess($scopeId = 141): void
    {
        $this->setWorkstation();

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
}
