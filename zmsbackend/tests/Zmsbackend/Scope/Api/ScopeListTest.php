<?php

namespace BO\Zmsbackend\Tests\Scope\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ScopeList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('restrictedscope');
        $response = $this->render([], [], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render([], [], []);
        $this->assertStringContainsString('scope.json', (string)$response->getBody());
        $this->assertStringContainsString('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
