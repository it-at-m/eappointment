<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Appointment\AppointmentCancelService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentCancelServiceTest extends MiddlewareTestCase
{
    private AppointmentCancelService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentCancelService();
        $this->reflector = new \ReflectionClass(AppointmentCancelService::class);
    }

    protected function tearDown(): void
    {
        \App::$now = null;
        parent::tearDown();
    }

    private function invokePrivateMethod(string $methodName, array $params = []): mixed
    {
        $method = $this->reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->service, $params);
    }

    public function testExtractClientDataWithValidInput(): void
    {
        $body = [
            'processId' => '12345',
            'authKey' => 'abc123'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('abc123', $result->authKey);
    }

    public function testExtractClientDataWithInvalidProcessId(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => 'abc123'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertNull($result->processId);
        $this->assertEquals('abc123', $result->authKey);
    }

    public function testExtractClientDataWithEmptyAuthKey(): void
    {
        $body = [
            'processId' => '12345',
            'authKey' => ''
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertNull($result->authKey);
    }

    public function testValidateClientDataWithValidData(): void
    {
        $data = (object)[
            'processId' => 12345,
            'authKey' => 'abc123'
        ];
    
        $result = $this->invokePrivateMethod('validateClientData', [$data]);
    
        $this->assertEquals(['errors' => []], $result);
    }

    public function testValidateClientDataWithInvalidData(): void
    {
        $data = (object)[
            'processId' => null,
            'authKey' => null
        ];

        $result = $this->invokePrivateMethod('validateClientData', [$data]);

        $this->assertArrayHasKey('errors', $result);
    }

    public function testCanBeCancelledWithFutureAppointment(): void
    {
        \App::$now = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
        
        $process = $this->createMock(ThinnedProcess::class);
        $process->timestamp = '1704114000'; // 2024-01-01 13:00:00 UTC (1 hour in future)
    
        $result = $this->invokePrivateMethod('canBeCancelled', [$process]);
    
        $this->assertTrue($result);
    }
    
    public function testCanBeCancelledWithPastAppointment(): void
    {
        \App::$now = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
        
        $process = $this->createMock(ThinnedProcess::class);
        $process->status = 'confirmed';  // Ensure process is in confirmed state
        $process->timestamp = '1704110400'; // 2024-01-01 11:00:00 UTC (1 hour in past)
    
        $result = $this->invokePrivateMethod('canBeCancelled', [$process]);
    
        $this->assertFalse($result);
    }

    public function testProcessCancelWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processCancel($body);

        $this->assertArrayHasKey('errors', $result);
    }
}