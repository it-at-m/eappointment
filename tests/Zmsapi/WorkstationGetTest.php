<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class WorkstationGetTest extends Base
{
    protected $classname = "WorkstationGet";

    public static $loginName = 'superuser';

    public static $authKey = 'vorschau';

    public static $basicAuth = 'dGVzdGFkbWluOjFwYWxtZTE=';

    public function __construct()
    {
        parent::__construct();
        static::$loginName = (! \App::DEBUG) ? static::$loginName : 'testadmin';
        static::$authKey = (! \App::DEBUG) ? static::$authKey : 'vorschau';
        static::$basicAuth = (! \App::DEBUG) ? static::$basicAuth : 'dGVzdGFkbWluOnZvcnNjaGF1';
    }

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            'resolveReferences' => 3
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertNotContains('"reducedData"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReadWorkstationByXAuthKey()
    {
        $workstation = (new \BO\Zmsdb\Workstation)
            ->writeEntityLoginByName(static::$loginName, static::$authKey, \App::getNow(), 1);
        $logInHash = (new \BO\Zmsdb\Workstation)->readLoggedInHashByName($workstation->getUseraccount()->id);
        $response = $this->render([], [
            '__header' => [
                'X-AuthKey' => $logInHash,
            ],
            'resolveReferences' => 0
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertContains(static::$loginName, (string)$response->getBody());
    }

    public function testReadWorkstationByBasicAuth()
    {
        $response = $this->render([], [
            '__header' => [
                'Authorization' => 'Basic '. static::$basicAuth,
            ],
            '__userinfo' => [
                'username' => static::$loginName,
                'password' => static::$authKey
            ],
            'resolveReferences' => 0
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertContains(static::$loginName, (string)$response->getBody());
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
