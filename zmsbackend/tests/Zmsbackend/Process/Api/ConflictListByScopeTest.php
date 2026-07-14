<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use \BO\Slim\Render;

class ConflictListByScopeTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ConflictListByScope";

    public function testRendering()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $response = $this->render(['id' => 141], [
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-06'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->render(['id' => 123], [], []);
    }
}
