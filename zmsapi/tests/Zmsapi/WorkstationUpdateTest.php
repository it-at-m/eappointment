<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationUpdateTest extends Base
{
    protected $classname = "WorkstationUpdate";

    const PLACE = '12';

    const SCOPEID = 141;

    public static $loginName = 'testadmin';

    public static $authKey = '128196aca512b2989d1d442455a57629';

    public function __construct()
    {
        parent::__construct();
    }

    public function testOveragedLogin()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\AuthKeyFound');
        $this->expectExceptionCode(200);

        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->lastLogin = 1447926465; //19.11.2015;

        $this->render([], ['__body' => json_encode($workstation)], []);
    }

    /*public function testAssignedWorkstationExists()
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
    }*/

    public function testAssignedWorkstationExistsByScopeAndNumber()
    {
        User::$assignedWorkstation = null;

        $this->expectException('\BO\Zmsapi\Exception\Workstation\WorkstationAlreadyAssigned');
        $this->expectExceptionCode(200);

        $entity = (new \BO\Zmsdb\Workstation)
            ->writeEntityLoginByName(static::$loginName, static::$authKey, \App::getNow(), 2);
        $entity->scope['id'] = self::SCOPEID;
        $entity->name = self::PLACE;
        (new \BO\Zmsdb\Workstation)->updateEntity($entity, 0);

        $workstation = $this->setWorkstation();
        $workstation->name = $entity->name;
        $workstation->id = 123;
        $workstation->scope['id'] = $entity->scope['id'];
        
        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }

    public function testAccessFailed()
    {
        $this->expectException('BO\Zmsapi\Exception\Workstation\WorkstationAccessFailed');
        $this->expectExceptionCode(404);
        $currentworkstation = $this->setWorkstation();
        $workstation = clone $currentworkstation;
        $workstation->getUseraccount()->id = static::$loginName;
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
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
