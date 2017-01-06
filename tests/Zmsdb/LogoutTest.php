<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\Useraccount as UserAccountEntity;

class LogoutTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        //superuser bo
        $userAccount = new UserAccountEntity(array(
            'id' => 'superuser',
            'password' => 'vorschau'
        ));

        $workstation = $query->writeEntityLoginByName($userAccount->id, $userAccount->password, $now);
        $this->assertEquals(true, $workstation->hasAuthKey());
        $workstation = $query->writeEntityLogoutByName($userAccount->id);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(false, $workstation->hasAuthKey());
        $this->assertEquals($now->format('Y-m-d'), date('Y-m-d', $workstation->useraccount['lastLogin']));
    }
}
