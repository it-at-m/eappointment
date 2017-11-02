<?php

namespace BO\Zmsapi\Tests;

class WorkstationLoginTest extends Base
{
    protected $classname = "WorkstationLogin";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => $this->readFixture('GetUseraccount.json'),
            'nocommit' => 1
        ], []);
        $this->assertContains('workstation.json', (string)$response->getBody());
        $this->assertContains('testadmin', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuthKeyFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\AuthKeyFound');
        $this->expectExceptionCode(200);
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->lastLogin = 1447926465;
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount.json'),
            'nocommit' => 1
        ], []);
    }

    public function testAlreadyLoggedIn()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount.json'),
            'nocommit' => 1
        ], []);
        $this->render([], [
            '__body' => $this->readFixture('GetUseraccount.json'),
            'nocommit' => 1
        ], []);
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Useraccount\UseraccountNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "password": "unittest"
            }'
        ], []);
    }
}
