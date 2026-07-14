<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessListByScopeAndStatusTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessListByScopeAndStatus";

    public function testRendering()
    {
        $this->setWorkstation();

        User::$workstation->useraccount->addDepartment(new \BO\Zmsentities\Department([
            'id' => 1,
            'scopes' => [
                ['id' => 141],
            ],
        ]));

        $response = $this->render(['id' => 141, 'status' => 'confirmed'], [], []);
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'status' => 'pending'], [], []);
    }
}
