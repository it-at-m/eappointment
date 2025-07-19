<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationGetTest extends Base
{
    protected $classname = "WorkstationGet";

    public static $loginName = 'testadmin';

    public static $password = 'vorschau'; 

    public static $basicAuth = 'dGVzdGFkbWluOnZvcnNjaGF1';



    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            'resolveReferences' => 3
        ], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringNotContainsString('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReadWorkstationByXAuthKey()
    {
        $workstation = (new \BO\Zmsdb\Workstation)
            ->writeEntityLoginByName(static::$loginName, md5(static::$password), \App::getNow(), (new \DateTime())->setTimestamp(time() + \App::SESSION_DURATION), 1);
        $sessionId = $workstation->authkey;
        $response = $this->render([], [
            '__header' => [
                'X-AuthKey' => $sessionId,
            ],
            'resolveReferences' => 0
        ], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringContainsString(static::$loginName, (string)$response->getBody());
    }

    public function testReadWorkstationByBasicAuth()
    {
        $response = $this->render([], [
            '__header' => [
                'Authorization' => 'Basic '. static::$basicAuth,
            ],
            '__userinfo' => [
                'username' => static::$loginName,
                'password' => static::$password
            ],
            'resolveReferences' => 0
        ], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringContainsString(static::$loginName, (string)$response->getBody());
    }

    public function testReadWorkstationByWrongBasicAuth()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $response = $this->render([], [
            '__header' => [
                'Authorization' => 'Basic dGVzdGFkbWluOjFwYWxtZTE=',
            ],
            '__userinfo' => [
                'username' => 'unittest',
                'password' => 'unittest'
            ],
            'resolveReferences' => 0
        ], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }

    public function testWithOrganisationRight()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('organisation');
        $this->setDepartment(74);
        $department = User::checkDepartment(74);
        $this->assertEquals(74, $department->getId());
    }

    public function testDepartmentWithoutLogin()
    {
        $this->expectException('\BO\Zmsentities\Exception\UseraccountMissingLogin');
        User::checkDepartment(74);
    }
}