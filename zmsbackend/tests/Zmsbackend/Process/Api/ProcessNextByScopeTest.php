<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessNextByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessNextByScope";

    public function testRendering()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
    }

    public function testIsReserved()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $entity = (new \BO\Zmsbackend\Process\Service\Process)->readEntity(82252, new \BO\Zmsbackend\Helper\NoAuth);
        $entity->status = 'reserved';
        $now = \App::getNow();
        (new \BO\Zmsbackend\Process\Service\Process)->updateEntity($entity, $now);
        $response = $this->render(['id' => 141], [], []);
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
