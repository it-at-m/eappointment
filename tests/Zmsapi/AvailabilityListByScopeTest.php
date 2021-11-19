<?php

namespace BO\Zmsapi\Tests;

class AvailabilityListByScopeTest extends Base
{
    protected $classname = "AvailabilityListByScope";

    const SCOPE_ID = 141;
    
    public function testRendering()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setRights('availability')->addDepartment($department);
        $response = $this->render(['id' => self::SCOPE_ID], [], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('availability');
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('availability');
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testNoAvailabilities()
    {
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => 1]);
        $this->setWorkstation()->getUseraccount()->setRights('availability')->addDepartment($department);
        $this->render(['id' => 1], [], []);
    }

    public function testMissingAccessRights()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setRights('scope')->addDepartment($department);
        $this->render(['id' => self::SCOPE_ID], [], []);
    }

    public function testMissingAccessRightsWithToken()
    {
        $department = (new \BO\Zmsentities\Department());
        $department->scopes[] = new \BO\Zmsentities\Scope(['id' => self::SCOPE_ID]);
        $this->setWorkstation()->getUseraccount()->setRights('scope')->addDepartment($department);
        $response =  $this->render(
            ['id' => self::SCOPE_ID],
            ['__header' => array(
                'X-Token' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4'
            )],
            []
        );
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
