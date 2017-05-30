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
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertNotContains('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedDataAccess()
    {
        $response = $this->render([], [], []);
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
