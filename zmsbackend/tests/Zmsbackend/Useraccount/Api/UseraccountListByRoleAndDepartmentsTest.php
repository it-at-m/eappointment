<?php

namespace BO\Zmsbackend\Tests\Useraccount\Api;

class UseraccountListByRoleAndDepartmentsTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = 'UseraccountListByRoleAndDepartments';

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser', 'useraccount');

        $response = $this->render(['roleName' => 'agent_queue', 'ids' => '74'], [], []);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('testuser', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);

        $this->render(['roleName' => 'agent_queue', 'ids' => '74'], [], []);
    }
}