<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Services\Appointment\AppointmentConfirmService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentConfirmServiceTest extends MiddlewareTestCase
{
    private AppointmentConfirmService $service;
    private \ReflectionClass $reflector;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentConfirmService();
        $this->reflector = new \ReflectionClass(AppointmentConfirmService::class);
    }

    #[\Override]
    protected function tearDown(): void
    {
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
            'authKey' => 'fb43'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('fb43', $result->authKey);
        $this->assertNull($result->sourceProcessId);
        $this->assertNull($result->sourceAuthKey);
    }

    public function testExtractClientDataWithSourceAppointment(): void
    {
        $body = [
            'processId' => '12345',
            'authKey' => 'fb43',
            'sourceProcessId' => '100001',
            'sourceAuthKey' => 'abcd'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('fb43', $result->authKey);
        $this->assertEquals(100001, $result->sourceProcessId);
        $this->assertEquals('abcd', $result->sourceAuthKey);
    }

    public function testExtractClientDataWithInvalidProcessId(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => 'fb43'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertNull($result->processId);
        $this->assertEquals('fb43', $result->authKey);
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
            'authKey' => 'fb43'
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

    public function testProcessConfirmWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processConfirm($body, null);

        $this->assertArrayHasKey('errors', $result);
    }

}