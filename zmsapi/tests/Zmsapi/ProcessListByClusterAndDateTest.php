<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;

class ProcessListByClusterAndDateTest extends Base
{
    protected $classname = "ProcessListByClusterAndDate";

    public function setUp(): void
    {
        parent::setUp();

        \App::$cache = null;
    }

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $response = $this->render(['id' => 109, 'date' => '2016-04-01'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'date' => '2016-04-01'], [], []);
    }

    public function testProviderSlotTimeIsPresentInProcessScope()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('cluster');
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
