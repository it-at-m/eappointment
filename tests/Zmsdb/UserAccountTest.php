<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\UserAccount as Query;
use \BO\Zmsdb\Workstation;
use \BO\Zmsentities\Useraccount as Entity;
use \BO\Zmsentities\Workstation as WorkstationEntity;

class UserAccountTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity('berlinonline', 1);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $entity);
    }

    public function testReadByAuthKey()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input, 2);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);

        $userAccount->setRights('organisation');
        $userAccount = $query->updateEntity($userAccount->id, $userAccount, 2);

        $workstation = (new Workstation())->writeEntityLoginByName($userAccount->id, $input->password, $now, 2);
        $this->assertEquals(true, $workstation->hasAuthKey());

        $userAccount = $query->readEntityByAuthKey($workstation->authkey, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);
    }

    public function testReadList()
    {
        $query = new Query();
        $entityList = $query->readList(2);
        $this->assertEntityList("\\BO\\Zmsentities\\Useraccount", $entityList);
        $this->assertEquals(true, $entityList->hasEntity('berlinonline')); //superuser bo
    }

    public function testReadListByDepartment()
    {
        $query = new Query();
        $entityList = $query->readCollectionByDepartmentId(74);
        $this->assertEntityList("\\BO\\Zmsentities\\Useraccount", $entityList);
        $this->assertEquals(true, $entityList->hasEntity('testuser')); //superuser bo
        $this->assertTrue($entityList->count() == 1, "Too much results for Department 74");
    }

    public function testReadAssignedDepartmentList()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $userAccount = $query->writeEntity($entity);
        $departmentList = $query->readAssignedDepartmentList($userAccount, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Department", $departmentList);
    }

    public function testReadWorkstationByScopeAndDay()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        //first write userAccount example in Database
        $userAccount = $query->writeEntity($input);
        //login workstation by useraccount
        $workstation = (new Workstation())->writeEntityLoginByName($userAccount->id, $input->password, $now);
        //get example workstation account with scope etc and give id from logged in workstation for update
        $workstationInput = (new WorkstationEntity())->getExample();
        $workstationInput->id = $workstation->id;
        //update workstation to read by scope testing
        $workstation = (new Workstation())->updateEntity($workstationInput);
        $workstationList = (new Workstation())->readByScopeAndDay(123, $now);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
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
