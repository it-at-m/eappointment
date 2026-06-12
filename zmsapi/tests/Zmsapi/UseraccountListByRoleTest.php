<?php

namespace BO\Zmsapi\Tests;

class UseraccountListByRoleTest extends Base
{
    protected $classname = 'UseraccountListByRole';

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser', 'useraccount');

        $response = $this->render(['roleName' => 'agent_queue'], [], []);
        $body = (string) $response->getBody();

        $this->assertStringContainsString('agent_queue', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('BO\\Zmsentities\\Exception\\UserAccountMissingRights');
        $this->expectExceptionCode(403);

        $this->render(['roleName' => 'agent_queue'], [], []);
    }
}
