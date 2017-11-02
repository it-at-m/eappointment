<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationUpdateTest extends Base
{
    protected $classname = "WorkstationUpdate";

    const SCOPE_ID = 141;

    const PLACE = "12";

    const NEWPLACE = "13";

    const TESTUSER = "testuser";

    const LASTLOGIN = 1459504500; //1.4.2016 11:55

    public function testOveragedLogin()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\AuthKeyFound');
        $this->expectExceptionCode(200);

        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->lastLogin = 1447926465; //19.11.2015;

        $this->render([], ['__body' => json_encode($workstation)], []);
    }

    public function testAssignedWorkstationExists()
    {
        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationAlreadyAssigned');
        $this->expectExceptionCode(200);

        $workstation = $this->setWorkstation();
        $workstation->name = self::PLACE;
        $workstation->id = 123;

        User::$assignedWorkstation = $this->setWorkstation();
        User::$assignedWorkstation->name = self::PLACE;

        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }

    public function testAssignedWorkstationExistsByScopeAndNumber()
    {
        User::$assignedWorkstation = null;

        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationAlreadyAssigned');
        $this->expectExceptionCode(200);

        $entity = (new \BO\Zmsdb\Workstation)->writeEntityLoginByName('testadmin', 'vorschau', \App::getNow(), 2);
        $entity->scope['id'] = 141;
        $entity->name = self::PLACE;
        (new \BO\Zmsdb\Workstation)->updateEntity($entity, 0);

        $workstation = $this->setWorkstation();
        $workstation->name = $entity->name;
        $workstation->id = 138;
        $workstation->scope['id'] = $entity->scope['id'];


        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }

    public function testAccessFailed()
    {
        $this->setExpectedException('BO\Zmsapi\Exception\Workstation\WorkstationAccessFailed');
        $this->expectExceptionCode(404);
        $currentworkstation = $this->setWorkstation();
        $workstation = clone $currentworkstation;
        $workstation->getUseraccount()->id = 'testadmin';
        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $response = $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
