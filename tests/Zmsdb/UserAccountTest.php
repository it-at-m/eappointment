<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\UserAccount as Query;
use \BO\Zmsdb\Workstation;
use \BO\Zmsentities\UserAccount as Entity;

class UserAccountTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity('berlinonline', 1);
        $this->assertEntity("\\BO\\Zmsentities\\UserAccount", $entity);
    }

    public function testReadByAuthKey()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input);

        $userAccount->setRights('organisation');
        $userAccount = $query->updateEntity($userAccount->id, $userAccount);

        $workstation = (new Workstation())->writeEntityLoginByName($userAccount->id, $input->password);
        $this->assertEquals(true, $workstation->hasAuthKey());

        $userAccount = $query->readEntityByAuthKey($workstation->authKey, 1);
        $this->assertEntity("\\BO\\Zmsentities\\UserAccount", $userAccount);
    }

    public function testReadList()
    {
        $query = new Query();
        $entityList = $query->readList();
        $this->assertEntityList("\\BO\\Zmsentities\\UserAccount", $entityList);
        $this->assertEquals(true, $entityList->hasEntity('berlinonline')); //superuser bo
    }

    public function testDublicate()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input);
        $this->assertTrue(!$userAccount->hasId(), "Dublicate UserAccount Entry found in DB.");
    }

    public function testDelete()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $deleteTest = $query->deleteEntity($input->id);
        $this->assertTrue($deleteTest, "Failed to delete User from Database.");
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->getExample();
    }
}
