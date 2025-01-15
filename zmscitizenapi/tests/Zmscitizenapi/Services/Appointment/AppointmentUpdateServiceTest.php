<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\Appointment\AppointmentUpdateService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentUpdateServiceTest extends MiddlewareTestCase
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
            'authKey' => 'abc123',
            'familyName' => 'Doe',
            'email' => 'john@example.com',
            'telephone' => '1234567890',
            'customTextfield' => 'Custom Info'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('abc123', $result->authKey);
        $this->assertEquals('Doe', $result->familyName);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('1234567890', $result->telephone);
        $this->assertEquals('Custom Info', $result->customTextfield);
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
        $this->assertNull($result->familyName);
        $this->assertNull($result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
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
            'authKey' => 'abc123',
            'familyName' => 'Doe',
            'email' => 'john@example.com',
            'telephone' => '1234567890',
            'customTextfield' => 'Custom Info'
        ];
    
        $result = $this->invokePrivateMethod('validateClientData', [$data]);
    
        $this->assertEquals(['errors' => []], $result);
    }

    public function testValidateClientDataWithInvalidData(): void
    {
        $data = (object)[
            'processId' => null,
            'authKey' => null,
            'familyName' => null,
            'email' => null,
            'telephone' => null,
            'customTextfield' => null
        ];

        $result = $this->invokePrivateMethod('validateClientData', [$data]);

        $this->assertArrayHasKey('errors', $result);
    }

    public function testUpdateProcessWithClientData(): void
    {
        $process = $this->createMock(ThinnedProcess::class);
        $process->familyName = 'Old Name';
        $process->email = 'old@example.com';
        
        $data = (object)[
            'familyName' => 'New Name',
            'email' => 'new@example.com',
            'telephone' => null,
            'customTextfield' => null
        ];

        $result = $this->invokePrivateMethod('updateProcessWithClientData', [$process, $data]);

        $this->assertEquals('New Name', $result->familyName);
        $this->assertEquals('new@example.com', $result->email);
        $this->assertNull($result->telephone);
        $this->assertNull($result->customTextfield);
    }

    public function testProcessUpdateWithValidationErrors(): void
    {
        $body = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->processUpdate($body);

        $this->assertArrayHasKey('errors', $result);
    }
}