<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Workstation as Entity;
use \BO\Zmsentities\Useraccount as UserAccountEntity;

class LoginTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $now = static::$now;
        //superuser bo
        $userAccount = new UserAccountEntity(array(
            'id' => static::$username
        ));

        $workstation = $query->writeEntityLoginByName($userAccount->id, md5(static::$password), $now, (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION));
        $this->assertEquals(true, $workstation->hasAuthKey());

        $workstation->scope['id'] = 141; //Bürgeramt Heerstraße
        $workstation->name = 12; //Arbeitsplatznummer

        $userAccount->addDepartment((new \BO\Zmsdb\Department())->readEntity('72')); //Bürgeramt Egon-Erwin-Kisch-Str.
        $workstation->useraccount = $userAccount;

        $workstation = $query->updateEntity($workstation);
        $this->assertEquals(true, !isset($workstation->authKey));

        $workstation = $query->readEntity($userAccount->id, 3);
        $this->assertEquals('Bürgeramt Heerstraße', $workstation->scope['contact']['name']);

        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
        $this->assertEquals(true, $userAccount->hasId());
    }
}
