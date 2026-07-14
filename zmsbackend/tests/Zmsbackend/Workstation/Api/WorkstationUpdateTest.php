<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationUpdateTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationUpdate";

    const PLACE = '12';

    const SCOPEID = 141;

    public static $loginName = 'testadmin';

    public static $authKey = '128196aca512b2989d1d442455a57629';



    public function testOveragedLogin()
    {
        $this->expectException('\BO\Zmsbackend\Useraccount\Exception\AuthKeyFound');
        $this->expectExceptionCode(200);

        $workstation = $this->setWorkstation();
        $this->setDepartment(74);
        $workstation->getUseraccount()->lastLogin = 1447926465; //19.11.2015;

        $this->render([], ['__body' => json_encode($workstation)], []);
    }

    // No longer the case
    /*public function testAssignedWorkstationExists()
    {
        
        $this->expectException('\BO\Zmsbackend\Workstation\Exception\WorkstationAlreadyAssigned');
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
    
    // No longer the case
    /*public function testAssignedWorkstationExistsByScopeAndNumber()
    {
        User::$assignedWorkstation = null;

        $this->expectException('\BO\Zmsbackend\Workstation\Exception\WorkstationAlreadyAssigned');
        $this->expectExceptionCode(200);

        $entity = (new \BO\Zmsbackend\Workstation\Service\Workstation)
            ->writeEntityLoginByName(static::$loginName, static::$authKey, \App::getNow(), (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION), 2);
        $entity->scope['id'] = self::SCOPEID;
        $entity->name = self::PLACE;
        (new \BO\Zmsbackend\Workstation\Service\Workstation)->updateEntity($entity, 0);

        $workstation = $this->setWorkstation();
        $workstation->name = $entity->name;
        $workstation->id = 123;
        $workstation->scope['id'] = $entity->scope['id'];
        
        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }*/

    public function testAccessFailed()
    {
        $this->expectException('BO\Zmsbackend\Workstation\Exception\WorkstationAccessFailed');
        $this->expectExceptionCode(404);
        $currentworkstation = $this->setWorkstation();
        $this->setDepartment(74);
        $workstation = clone $currentworkstation;
        $workstation->getUseraccount()->id = static::$loginName;
        $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
    }

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $this->setDepartment(74);
        $response = $this->render([], [
            '__body' => json_encode($workstation)
        ], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
