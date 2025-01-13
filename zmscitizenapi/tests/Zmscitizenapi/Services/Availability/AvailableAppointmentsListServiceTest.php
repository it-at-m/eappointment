<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Services\Availability\AvailableAppointmentsListService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AvailableAppointmentsListServiceTest extends MiddlewareTestCase
{
    private AvailableAppointmentsListService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailableAppointmentsListService();
        $this->reflector = new \ReflectionClass(AvailableAppointmentsListService::class);
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
            'date' => '2024-01-01',
            'officeId' => '12345',
            'serviceId' => '1,2,3',
            'serviceCount' => '1,2,3'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertEquals('2024-01-01', $result->date);
        $this->assertEquals(12345, $result->officeId);
        $this->assertEquals(['1', '2', '3'], $result->serviceIds);
        $this->assertEquals(['1', '2', '3'], $result->serviceCounts);
    }

    public function testExtractClientDataWithInvalidInput(): void
    {
        $queryParams = [
            'date' => '',
            'officeId' => 'invalid',
            'serviceId' => '',
            'serviceCount' => ''
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertEmpty($result->date);
        $this->assertEquals(0, $result->officeId);
        $this->assertEquals([''], $result->serviceIds);
        $this->assertEquals([''], $result->serviceCounts);
    }

    public function testValidateClientDataWithValidData(): void
    {
        $data = (object)[
            'date' => '2024-01-01',
            'officeId' => 12345,
            'serviceIds' => ['1', '2', '3'],
            'serviceCounts' => ['1', '2', '3']
        ];
    
        $result = $this->invokePrivateMethod('validateClientData', [$data]);
    
        $this->assertEquals(['errors' => []], $result);
    }

    public function testValidateClientDataWithInvalidData(): void
    {
        $data = (object)[
            'date' => null,
            'officeId' => null,
            'serviceIds' => null,
            'serviceCounts' => null
        ];

        $result = $this->invokePrivateMethod('validateClientData', [$data]);

        $this->assertArrayHasKey('errors', $result);
    }

    public function testGetAvailableAppointmentsListWithValidationErrors(): void
    {
        $queryParams = [
            'date' => '',
            'officeId' => 'invalid',
            'serviceId' => '',
            'serviceCount' => ''
        ];

        $result = $this->service->getAvailableAppointmentsList($queryParams);

        $this->assertArrayHasKey('errors', $result);
    }

}