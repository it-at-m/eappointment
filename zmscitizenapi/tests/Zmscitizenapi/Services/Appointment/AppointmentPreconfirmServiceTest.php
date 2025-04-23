<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Services\Appointment\AppointmentPreconfirmService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentPreconfirmServiceTest extends MiddlewareTestCase
{
    private AppointmentPreconfirmService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentPreconfirmService();
        $this->reflector = new \ReflectionClass(AppointmentPreconfirmService::class);
    }

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

    public function testProcessPreconfirmWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processPreconfirm($body);

        $this->assertArrayHasKey('errors', $result);
    }

}