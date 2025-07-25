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
        \App::$now = new \DateTimeImmutable('2016-05-16 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        $response = $this->render(['id' => 10030], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFutureAppointmentAccessibleForEditing()
    {
        \App::$now = new \DateTimeImmutable('2016-05-15 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        
        // Future appointments should now be accessible for editing
        $response = $this->render(['id' => 10030], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testPastAppointmentAccessibleForEditing()
    {
        \App::$now = new \DateTimeImmutable('2016-05-17 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        
        // Past appointments should now be accessible for editing
        $response = $this->render(['id' => 10030], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWorkstationProcessMatchScopeFailed()
    {
        \App::$now = new \DateTimeImmutable('2016-05-16 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 143);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => 10030], [], []);
    }

    public function testSameDateDifferentTimes()
    {
        \App::$now = new \DateTimeImmutable('2016-05-16 08:00:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $response = $this->render(['id' => 10030], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMidnightBoundaryCase()
    {
        \App::$now = new \DateTimeImmutable('2016-05-16 23:59:59', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 141);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $response = $this->render(['id' => 10030], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProcessWithoutAppointments()
    {
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render(['id' => 100031], [], []);
        
    }

    public function testProcessNotCallablePreconfirmed()
    {
        \App::$now = new \DateTimeImmutable('2016-05-24 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotCallable');
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
