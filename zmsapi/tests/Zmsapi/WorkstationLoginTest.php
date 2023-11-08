<?php

namespace BO\Zmsapi\Tests;

class WorkstationLoginTest extends Base
{
    protected $classname = "WorkstationLogin";

    public static $useraccount = '';

    public static $loginName = 'superuser';

    public static $authKey = 'vorschau';

    public function __construct()
    {
        parent::__construct();
        static::$loginName = (! \App::DEBUG) ? static::$loginName : 'testadmin';
        static::$authKey = (! \App::DEBUG) ? static::$authKey : 'vorschau';
        static::$useraccount = json_decode($this->readFixture('GetUseraccount.json'), 1);
        static::$useraccount['id'] = static::$loginName;
        static::$useraccount['password'] = static::$authKey;
        static::$useraccount = json_encode(static::$useraccount);
    }

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => static::$useraccount,
            'nocommit' => 1
        ], []);
        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringContainsString(static::$loginName, (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthKeyFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\AuthKeyFound');
        $this->expectExceptionCode(200);
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->lastLogin = 1447926465;
        $this->render([], [
            '__body' => static::$useraccount,
            'nocommit' => 1
        ], []);
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\InvalidCredentials');
        $this->expectExceptionCode(401);
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount_unknown.json')
        ], []);
    }
}
