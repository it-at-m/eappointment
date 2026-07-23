<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;

class ProcessListByClusterAndDateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByClusterAndDate";

    public function setUp(): void
    {
        parent::setUp();

        \App::$cache = null;
    }

    public function testRendering()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));
        $response = $this->render(['id' => 109, 'date' => '2016-04-01'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Cluster\Exception\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'date' => '2016-04-01'], [], []);
    }

    public function testProviderSlotTimeIsPresentInProcessScope()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment', 'waitingqueue');
        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));
        $response = $this->render(['id' => 109, 'date' => '2016-04-01'], [], []);
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
