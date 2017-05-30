<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationGetTest extends Base
{
    protected $classname = "WorkstationGet";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertNotContains('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
