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
        $this->assertEntity("\\BO\\Zmsentities\\UserAccount", $userAccount);

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

    public function testReadAssignedDepartmentList()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $userAccount = $query->writeEntity($entity);
        $departmentList = $query->readAssignedDepartmentList($userAccount, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Department", $departmentList);
    }

    public function testDelete()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $userAccount = $query->writeEntity($entity);
        $query->deleteEntity($userAccount->id);
        $entity = $query->readEntity($userAccount->id, 1);
        $this->assertFalse(isset($entity->id), "Failed to delete User from Database.");
    }

    public function testDublicate()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input);
        $userAccount = $query->writeEntity($input);
        $query->deleteEntity($userAccount->id);
        $this->assertFalse($query->readIsUserExisting($userAccount->id), "Dublicate UserAccount Entry found in DB.");
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
