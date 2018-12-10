<?php

namespace BO\Zmsapi\Tests;

class AppointmentUpdateTest extends Base
{
    protected $classname = "AppointmentUpdate";

    const PROCESS_ID = 94860;

    const AUTHKEY = 'cdce';

    public function testRendering()
    {
        \App::$now = \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
        ], []);
        $this->assertContains('"date":1464588000', (string)$response->getBody());
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [], []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->render(['id' => 123456, 'authKey' => 'abcd'], [
            '__body' => $this->readFixture('PostAppointment.json'),
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->render(['id' => self::PROCESS_ID, 'authKey' => 'abcd'], [
            '__body' => $this->readFixture('PostAppointment.json'),
        ], []);
    }
}
