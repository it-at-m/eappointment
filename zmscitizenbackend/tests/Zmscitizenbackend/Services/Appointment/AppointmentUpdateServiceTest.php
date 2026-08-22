<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Services\Appointment;

use PHPUnit\Framework\TestCase;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Services\Appointment\AppointmentUpdateService;

class AppointmentUpdateServiceTest extends TestCase
{
    private AppointmentUpdateService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentUpdateService();
        $this->reflector = new \ReflectionClass(AppointmentUpdateService::class);
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
            'authKey' => 'fb43',
            'familyName' => 'Doe',
            'email' => 'john@example.com',
            'telephone' => '1234567890',
            'customTextfield' => 'Custom Info',
            'customTextfield2' => 'Custom Info 2'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('fb43', $result->authKey);
        $this->assertEquals('Doe', $result->familyName);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('1234567890', $result->telephone);
        $this->assertEquals('Custom Info', $result->customTextfield);
        $this->assertEquals('Custom Info 2', $result->customTextfield2);
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
        $this->assertNull($result->familyName);
        $this->assertNull($result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
        $this->assertNull($result->customTextfield2);
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

    public function testUpdateProcessWithClientData(): void
    {
        $process = new ThinnedProcess();
        $process->familyName = 'Old Name';
        $process->email = 'old@example.com';
        
        $data = (object)[
            'familyName' => 'New Name',
            'email' => 'new@example.com',
            'telephone' => null,
            'customTextfield' => null,
            'customTextfield2' => null
        ];

        $result = $this->invokePrivateMethod('updateProcessWithClientData', [$process, $data]);

        $this->assertEquals('New Name', $result->familyName);
        $this->assertEquals('new@example.com', $result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
        $this->assertNull($result->customTextfield2);
    }

    public function testProcessUpdateWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processUpdate($body, null);

        $this->assertArrayHasKey('errors', $result);
    }
}
