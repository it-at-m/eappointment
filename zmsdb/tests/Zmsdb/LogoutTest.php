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
        $now = static::$now;
        //superuser bo
        $userAccount = new UserAccountEntity(array(
            'id' => static::$username,
            'password' => md5(static::$password)
        ));

        $workstation = $query->writeEntityLoginByName($userAccount->id, $userAccount->password, $now, (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION), 2);
        $this->assertEquals(true, $workstation->hasAuthKey());
        $workstation = $query->writeEntityLogoutByName($userAccount->id, 2);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(false, $workstation->hasAuthKey());
    }
}
