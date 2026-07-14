<?php

namespace BO\Zmsbackend\Tests\Logout\Service;

use \BO\Zmsbackend\Workstation\Service\Workstation as Query;
use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\Useraccount as UserAccountEntity;

class LogoutTest extends \BO\Zmsbackend\Tests\Service\Base
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

        $workstation = $query->writeEntityLoginByName($userAccount->id, $userAccount->password, $now, (new \DateTime())->setTimestamp(time() + 28800), 2);
        $this->assertEquals(true, $workstation->hasAuthKey());
        $workstation = $query->writeEntityLogoutByName($userAccount->id, 2);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(false, $workstation->hasAuthKey());
    }
}
