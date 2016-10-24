<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\UserAccount as UserAccountEntity;

class LogoutTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        //superuser bo
        $userAccount = new UserAccountEntity(array(
            'id' => 'superuser',
            'password' => 'vorschau'
        ));

        $workstation = $query->writeEntityLoginByName($userAccount->id, $userAccount->password);
        $this->assertEquals(true, $workstation->hasAuthKey());
        $workstation = $query->writeEntityLogoutByName($userAccount->id);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(false, $workstation->hasAuthKey());
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', $workstation->useraccount['lastLogin']));
    }
}
