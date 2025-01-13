<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Services\Appointment\AppointmentByIdService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentByIdServiceTest extends MiddlewareTestCase
{
    private AppointmentByIdService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentByIdService();
        $this->reflector = new \ReflectionClass(AppointmentByIdService::class);
    }

    private function mockStaticMethod($class, $method, $callback): void
{
    $mock = \Mockery::mock('alias:' . $class);
    $mock->shouldReceive()
        ->andReturnUsing($callback);
}

    private function invokePrivateMethod(string $methodName, array $params = []): mixed
    {
        $method = $this->reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->service, $params);
    }

    public function testExtractClientDataWithValidInput(): void
    {
        $queryParams = [
            'processId' => '12345',
            'authKey' => 'abc123'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertEquals(12345, $result->processId);
        $this->assertEquals('abc123', $result->authKey);
    }

    public function testExtractClientDataWithInvalidProcessId(): void
    {
        $queryParams = [
            'processId' => 'invalid',
            'authKey' => 'abc123'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertNull($result->processId);
        $this->assertEquals('abc123', $result->authKey);
    }

    public function testExtractClientDataWithEmptyAuthKey(): void
    {
        $queryParams = [
            'processId' => '12345',
            'authKey' => ''
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

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

    public function testGetAppointmentByIdWithValidationErrors(): void
    {
        $queryParams = [
            'processId' => 'invalid',
            'authKey' => ''
        ];

        $result = $this->service->getAppointmentById($queryParams);

        $this->assertArrayHasKey('errors', $result);
    }

    private function mockStaticFacadeMethod(string $method, array $params, mixed $returnValue): void
    {
        $facadeReflection = new \ReflectionClass(ZmsApiFacadeService::class);
        $mockMethod = $facadeReflection->getMethod($method);
        $mockMethod->setAccessible(true);

        // Create a mock that will return our expected value
        $mock = $this->getMockBuilder(ZmsApiFacadeService::class)
            ->disableOriginalConstructor()
            ->addMethods([$method])
            ->getMock();

        $mock->expects($this->once())
            ->method($method)
            ->with(...$params)
            ->willReturn($returnValue);

        // Replace the original class with our mock
        $mockProperty = $facadeReflection->getProperty('instance');
        $mockProperty->setAccessible(true);
        $mockProperty->setValue(null, $mock);
    }
}