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
        $this->assertContains('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertContains('process.json', (string)$response->getBody());
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

        $this->assertContains('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertContains('process.json', (string)$response->getBody());
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

        $this->assertContains('"date":1464588000', (string)$response->getBody()); // 2016-05-30 08:00
        $this->assertContains('process.json', (string)$response->getBody());
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
