<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\UserAccount as Query;
use \BO\Zmsdb\Workstation;
use \BO\Zmsentities\Useraccount as Entity;
use \BO\Zmsentities\Workstation as WorkstationEntity;

/**
 * @SuppressWarnings(Public)
 *
 */
class UserAccountTest extends Base
{
    public $dateTime = null;

    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity('berlinonline', 1);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $entity);
    }

    public function testReadByAuthKey()
    {
        $this->dateTime = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input, 2);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);

        $userAccount->setRights('organisation');
        $userAccount = $query->updateEntity($userAccount->id, $userAccount, 2);

        $workstation = (new Workstation())
            ->writeEntityLoginByName($userAccount->id, $input->password, $this->dateTime, 2);
        $this->assertEquals(true, $workstation->hasAuthKey());

        $userAccount = $query->readEntityByAuthKey($workstation->authkey, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);
    }

    public function testReadLoggedInHashByName()
    {
        $this->writeTestLogin();
        $hash = (new Workstation())->readLoggedInHashByName('johndoe');
        $this->assertTrue(null !== $hash);
    }

    public function testReadByUserId()
    {
        $query = new Query();
        $useraccount = $query->readEntityByUserId('137'); //testReadByUserId
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $useraccount);
        $this->assertEquals('testuser', $useraccount->id);
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
        $this->assertTrue($entityList->count() == 2, "Too much results for Department 74");
    }

    public function testReadAssignedDepartmentList()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $userAccount = $query->writeEntity($entity);
        $departmentList = $query->readAssignedDepartmentList($userAccount, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Department", $departmentList);
    }

    public function testReadWorkstationListByScope()
    {
        $this->writeTestLogin();
        $workstationList = (new Workstation())->readLoggedInListByScope(123, $this->dateTime);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
    }

    public function testReadWorkstationListByCluster()
    {
        $this->writeTestLogin(141);
        $workstationList = (new Workstation())->readLoggedInListByCluster(109, $this->dateTime);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
    }

    public function testReadWorkstationListByDepartment()
    {
        $this->writeTestLogin(141);
        $workstationList = (new Workstation())->readCollectionByDepartmentId(72);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
        $this->assertEquals(3, $workstationList->getFirst()->name);
    }

    public function testReadWorkstationByScopeAndName()
    {
        $this->writeTestLogin();
        $workstation = (new Workstation())->readWorkstationByScopeAndName(123, 3);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
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

    protected function writeTestLogin($scopeId = false)
    {
        $this->dateTime = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        //first write userAccount example in Database
        $userAccount = $query->writeEntity($input);
        //login workstation by useraccount
        $workstation = (new Workstation())->writeEntityLoginByName($userAccount->id, $input->password, $this->dateTime);
        //get example workstation account with scope etc and give id from logged in workstation for update
        $workstationInput = (new WorkstationEntity())->getExample();
        $workstationInput->id = $workstation->id;
        if ($scopeId) {
            $workstation->scope['id'] = $scopeId;
        }
        //update workstation to read by scope testing
        return (new Workstation())->updateEntity($workstationInput);
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
