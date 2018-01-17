<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessListByScopeAndDateTest extends Base
{
    protected $classname = "ProcessListByScopeAndDate";

    public function testRendering()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'date' => '2016-04-01'], [], []);
    }

    public function testWithResolveReferencesZero()
    {
        $this->setWorkstation();
        User::$workstation->useraccount->setRights('scope');
        $response = $this->render(['id' => 141, 'date' => '2016-04-01'], [], ['resolveReferences' => 0]);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
