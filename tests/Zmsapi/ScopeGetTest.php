<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeGetTest extends Base
{
    protected $classname = "ScopeGet";

    const SCOPE_ID = 141;

    public function testReducedDataAccess()
    {
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertContains('"reducedData":true', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertContains('scope.json', (string)$response->getBody());
        $this->assertNotContains('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
