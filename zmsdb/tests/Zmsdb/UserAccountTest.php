<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Useraccount as Query;
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
        $entity = $query->readEntity(static::$username, 1);
        $entity->email = "test@berlinonline.de";
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $entity);
    }

    public function testReadWorkstationFailed()
    {
        $entityFailed = (new Workstation)->readEntity('maxmuster', 1);
        $this->assertEquals(null, $entityFailed);
    }

    public function testReadByAuthKey()
    {
        $this->dateTime = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $userAccount = $query->writeEntity($input, 2);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);

        $userAccount->setRights('organisation');
        $userAccount = $query->writeUpdatedEntity($userAccount->id, $userAccount, 2);

        $workstation = (new Workstation())
            ->writeEntityLoginByName($userAccount->id, $input->password, $this->dateTime, 2);
        $this->assertEquals(true, $workstation->hasAuthKey());

        $userAccount = $query->readEntityByAuthKey($workstation->authkey, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $userAccount);
    }

    public function testReadLoggedInHashByName()
    {
        $workstation = $this->writeTestLogin();
        $hash = (new Workstation())->readLoggedInHashByName($workstation->useraccount->id);
        $this->assertTrue(null !== $hash);
    }

    public function testReadByUserId()
    {
        $query = new Query();
        $useraccount = $query->readEntityByUserId('137'); //testReadByUserId
        $useraccount->email = "test@berlinonline.de";
        $useraccount->departments[] = (new \BO\Zmsdb\Department())->readEntity(72);
        $this->assertEntity("\\BO\\Zmsentities\\Useraccount", $useraccount);
        $this->assertEquals('testuser', $useraccount->id);
    }

    public function testReadList()
    {
        $query = new Query();
        $entityList = $query->readList(2);
        foreach ($entityList as $entity) {
            $entity->departments[] = (new \BO\Zmsdb\Department())->readEntity(72);
            $entity->email = "test@berlinonline.de";
        }
        $this->assertEntityList("\\BO\\Zmsentities\\Useraccount", $entityList);
        $this->assertEquals(true, $entityList->hasEntity(static::$username)); //superuser bo
    }

    public function testReadListByDepartment()
    {
        $query = new Query();
        $entityList = $query->readCollectionByDepartmentId(74);
        foreach ($entityList as $entity) {
            $entity->departments[] = (new \BO\Zmsdb\Department())->readEntity(72);
            $entity->email = "test@berlinonline.de";
        }
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
        $workstationList = (new Workstation())->readLoggedInListByScope(141, $this->dateTime);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
    }

    public function testReadWorkstationListByCluster()
    {
        $this->writeTestLogin();
        $workstationList = (new Workstation())->readLoggedInListByCluster(109, $this->dateTime);
        $this->assertEquals(1, $workstationList->count());
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
    }

    public function testReadWorkstationListByDepartment()
    {
        $this->writeTestLogin();
        $workstationList = (new Workstation())->readCollectionByDepartmentId(72);
        $this->assertEntityList("\\BO\\Zmsentities\\Workstation", $workstationList);
        $this->assertEquals(3, $workstationList->getFirst()->name);
    }

    public function testReadWorkstationByScopeAndName()
    {
        $this->writeTestLogin();
        $workstation = (new Workstation())->readWorkstationByScopeAndName(141, 3);
        $this->assertEntity("\\BO\\Zmsentities\\Workstation", $workstation);
    }

    public function testReadWorkstationByScopeAndNameFailed()
    {
        $this->writeTestLogin();
        $workstation = (new Workstation())->readWorkstationByScopeAndName(141, 4);
        $this->assertEquals(null, $workstation);
    }

    public function testWriteRemovedProcess()
    {
        $now = static::$now;
        $workstation = $this->writeTestLogin();
        $process = (new \BO\Zmsdb\Process)->readEntity(10029, '1c56');
        $workstation->process = (new Workstation)->writeAssignedProcess($workstation, $process, $now);
        $workstation->process->queue['callCount'] = 1;
        (new Workstation)->writeRemovedProcess($workstation);
        $process = (new \BO\Zmsdb\Process)->readEntity(10029, '1c56');
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals(1, $process->queue['callCount']);
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

    public function testDuplicate()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $this->expectException('\BO\Zmsdb\Exception\Useraccount\DuplicateEntry');
        $userAccount = $query->writeEntity($input);
        $userAccount = $query->writeEntity($input);
    }

    public function testLoginFailed()
    {
        $now = static::$now;
        $this->expectException('\BO\Zmsdb\Exception\Useraccount\InvalidCredentials');
        (new Workstation())->writeEntityLoginByName('johndoe', 'secret', $now);
    }

    public function testWriteHintByName()
    {
        $workstation = $this->writeTestLogin();
        $workstationInput = (new WorkstationEntity())->getExample();
        $workstationInput->id = $workstation->id;
        $workstationInput->hint = '';
        $workstationInput['useraccount']['id'] = $workstation->useraccount->id;
        $workstation = (new Workstation())->updateEntity($workstationInput);
        $this->assertEquals('3', $workstation->hint);
    }

    protected function writeTestLogin()
    {
        $this->dateTime = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $input->id = $input->id . rand();
        //first write userAccount example in Database
        $userAccount = $query->writeEntity($input);
        //login workstation by useraccount
        $workstation = (new Workstation())->writeEntityLoginByName($userAccount->id, $input->password, $this->dateTime);
        //get example workstation account with scope etc and give id from logged in workstation for update
        $workstationInput = (new WorkstationEntity())->getExample();
        $workstationInput->id = $workstation->id;
        $workstationInput['useraccount']['id'] = $input->id;
        //update workstation to read by scope testing
        return (new Workstation())->updateEntity($workstationInput, 1);
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
