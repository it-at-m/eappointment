<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessNextByScopeTest extends Base
{
    protected $classname = "ProcessNextByScope";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
    }

    public function testIsReserved()
    {
        $this->setWorkstation();
        $entity = (new \BO\Zmsdb\Process)->readEntity(82252, new \BO\Zmsdb\Helper\NoAuth);
        $entity->status = 'reserved';
        $now = \App::getNow();
        (new \BO\Zmsdb\Process)->updateEntity($entity, $now);
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
