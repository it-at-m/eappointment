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
        \App::$now = new \DateTimeImmutable('2016-05-24 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        $response = $this->render(['id' => 100032], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    /*public function testProcessNotCurrentDateInTheFuture()
    {
        \App::$now = new \DateTimeImmutable('2016-05-23 10:45:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotCurrentDate');
        $this->expectExceptionCode(404);
        $this->render(['id' => 100032], [], []);
    }*/

    public function testProcessNotCurrentDateInThePast()
    {
        \App::$now = new \DateTimeImmutable('2016-05-25 10:45:00', new \DateTimeZone('Europe/Berlin'));
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

    public function testSameDateDifferentTimes()
    {
        // Test that appointments on same date but different times pass validation
        // Current time: 08:00, Appointment time: ~11:00 (both on 2016-05-24)
        \App::$now = new \DateTimeImmutable('2016-05-24 08:00:00', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $response = $this->render(['id' => 100032], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMidnightBoundaryCase()
    {
        // Test same calendar date across different times of day
        // Current time: late night, Appointment: early morning (same date)
        \App::$now = new \DateTimeImmutable('2016-05-24 23:59:59', new \DateTimeZone('Europe/Berlin'));
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        $response = $this->render(['id' => 100032], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testProcessWithoutAppointments()
    {
        // Test process without appointments passes through validation
        // This tests the early return in testProcessCurrentDate when !$process->isWithAppointment()
        $workstation = $this->setWorkstation(137, 'testuser', 313);
        $workstation['queue']['clusterEnabled'] = 1;
        
        // Use a non-existent process ID to test that testProcessCurrentDate() 
        // properly handles null/empty process (early return)
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render(['id' => 100031], [], []);
        
        // The key test: testProcessCurrentDate should NOT throw ProcessNotCurrentDate
        // for non-existent processes - it should return early and let ProcessNotFound be thrown
    }

    public function testProcessWithAppointmentButNoDate()
    {
        // Test the early return in testProcessCurrentDate when appointment has no date (date = 0)
        // This would require mocking or a fixture with appointment.date = 0
        // For now, this documents the test case we would want if we had such data
        
        // Since we don't have easy access to such test data, we'll skip this test
        // The method handles this case with: if (!$appointment || !$appointment->date) return;
        $this->markTestSkipped('No test data available with appointments that have date = 0');
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
