<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsclient\Http;
use Psr\SimpleCache\CacheInterface;

class ProcessListByClusterAndDateTest extends Base
{
    protected $classname = "ProcessListByClusterAndDate";
    protected CacheInterface $cacheMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->cacheMock = $this->createMock(CacheInterface::class);
        \App::$cache = $this->cacheMock;
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
}
