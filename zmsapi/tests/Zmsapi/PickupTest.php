<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class PickupTest extends Base
{
    protected $classname = "Pickup";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->scope['id'] = 141;
        
        $entity = (new \BO\Zmsdb\Process)->readEntity(10030, new \BO\Zmsdb\Helper\NoAuth);
        $entity->status = 'pending';
        $response = (new ProcessFinishedTest())->render([], [
            '__body' => json_encode($entity)
        ], []);
        $response = $this->render([], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"status":"pending"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testSelectedScope()
    {
        $this->setWorkstation();
        User::$workstation->scope['id'] = 380;

        $entity = (new \BO\Zmsdb\Process)->readEntity(10030, new \BO\Zmsdb\Helper\NoAuth);
        $entity->status = 'pending';
        $response = (new ProcessFinishedTest())->render([], [
            '__body' => json_encode($entity)
        ], []);
        $response = $this->render([], ['selectedScope' => 141], []);
        $this->assertStringContainsString('141', (string)$response->getBody());
        $this->assertStringContainsString('10030', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotMatchingScope()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);
        $this->setWorkstation();
        User::$workstation->scope['id'] = 143;

        $entity = (new \BO\Zmsdb\Process)->readEntity(10030, new \BO\Zmsdb\Helper\NoAuth);
        $entity->status = 'pending';
        $response = (new ProcessFinishedTest())->render([], [
            '__body' => json_encode($entity)
        ], []);

        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        User::$workstation->scope['id'] = 999;
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [], []);
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
