<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ScopeWithWorkstationCountTest extends Base
{
    protected $classname = "ScopeWithWorkstationCount";

    const SCOPE_ID = 141;

    public function testRendering()
    {
        $response = $this->render(['id' => self::SCOPE_ID], [], []); //Pankow
        $this->assertContains('"workstationCount"', (string)$response->getBody());
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
