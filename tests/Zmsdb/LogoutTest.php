<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Workstation as Entity;

class LogoutTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $workstation = $query->writeEntityLogoutByName('berlinonline');
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(0, $workstation->scope['id']);
        $this->assertEquals(0, current($workstation->useraccount['departments'])['id']);
        $this->assertEquals(false, $workstation->hasAuthKey());
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', $workstation->useraccount['lastLogin']));
    }
}
