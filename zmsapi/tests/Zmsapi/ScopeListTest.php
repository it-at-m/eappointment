<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeListTest extends Base
{
    protected $classname = "ScopeList";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
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
