<?php

namespace BO\Zmsapi\Tests;

class AppointmentUpdateTest extends Base
{
    protected $classname = "AppointmentUpdate";

    const PROCESS_ID = 94860; // with appointment date 1464595200 2016-05-30 10:00

    const AUTHKEY = 'cdce';

    public function testRendering()
    {
        \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
        ], []);
        $this->assertStringContainsString('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithSlotsRequired()
    {
        $this->setWorkstation();
        \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
            'slotsRequired' => 2,
            'slotType' => 'intern'
        ], []);

        $this->assertStringContainsString('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertStringContainsString('process.json', (string)$response->getBody());
    }

    public function testWithSlotsRequiredExceeded()
    {
        $this->expectException('BO\Zmsdb\Exception\Process\ProcessReserveFailed');
        $this->setWorkstation();
        \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
            'slotsRequired' => 20,
            'slotType' => 'intern'
        ], []);

        $this->assertStringContainsString('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertStringContainsString('process.json', (string)$response->getBody());
    }

    public function testWithClientKey()
    {
        $this->setWorkstation();
        \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
            'clientkey' => 'default'
        ], []);

        $this->assertStringContainsString('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertStringContainsString('process.json', (string)$response->getBody());
    }

    public function testWithClientKeyBlocked()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->setWorkstation();
        \App::$now->modify('2016-05-30');
        $response = $this->render(['id' => self::PROCESS_ID, 'authKey' => self::AUTHKEY], [
            '__body' => $this->readFixture('PostAppointment.json'),
            'clientkey' => '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE'
        ], []);
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
}
