<?php

namespace BO\Zmsapi\Tests;

class WorkstationProcessGetTest extends Base
{
    protected $classname = "WorkstationProcessGet";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        // Reset to test bootstrap default
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        parent::tearDown();
    }

    public function testRendering()
    {
        // Mock current date to May 24, 2016 to match the appointment date (1464086700)
        \App::$now = new \DateTimeImmutable('2016-05-24 10:45:00', new \DateTimeZone('Europe/Berlin'));
        // Set workstation to scope 313 to match process 100032's scope
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        $response = $this->render(['id' => 100032], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProcessNotCurrentDate()
    {
        // Use default test date (2016-04-01) but try to access process from May 24, 2016
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotCurrentDate');
        $this->expectExceptionCode(404);
        $this->render(['id' => 100032], [], []);
    }

    public function testWorkstationProcessMatchScopeFailed()
    {
        // Mock current date to May 24, 2016 to match appointment (pass date validation)
        \App::$now = new \DateTimeImmutable('2016-05-24 10:45:00', new \DateTimeZone('Europe/Berlin'));
        // Set workstation to different scope (143) than process scope (313) 
        $workstation = $this->setWorkstation(137, 'testuser', 143);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => 100032], [], []);
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->expectExceptionCode(401);
        $this->render([], [], []);
    }
}
