<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Services\Availability\AvailableDaysListService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AvailableDaysListServiceTest extends MiddlewareTestCase
{
    private AvailableDaysListService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailableDaysListService();
        $this->reflector = new \ReflectionClass(AvailableDaysListService::class);
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
            'officeId' => '12345',
            'serviceId' => '1',
            'serviceCount' => '1,2,3',
            'startDate' => '2024-01-01',
            'endDate' => '2024-01-31'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertEquals(12345, $result->officeId);
        $this->assertEquals(1, $result->serviceId);
        $this->assertEquals(['1', '2', '3'], $result->serviceCounts);
        $this->assertEquals('2024-01-01', $result->startDate);
        $this->assertEquals('2024-01-31', $result->endDate);
    }

    public function testExtractClientDataWithInvalidInput(): void
    {
        $queryParams = [
            'officeId' => 'invalid',
            'serviceId' => 'invalid',
            'serviceCount' => '',
            'startDate' => '',
            'endDate' => ''
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$queryParams]);

        $this->assertEmpty($result->officeId);
        $this->assertEmpty($result->serviceId);
        $this->assertEquals([], $result->serviceCounts);
        $this->assertEmpty($result->startDate);
        $this->assertEmpty($result->endDate);
    }

    public function testValidateClientDataWithValidData(): void
    {
        $data = (object)[
            'officeId' => 12345,
            'serviceId' => 1,
            'serviceCounts' => ['1', '2', '3'],
            'startDate' => '2024-01-01',
            'endDate' => '2024-01-31'
        ];
    
        $result = $this->invokePrivateMethod('validateClientData', [$data]);
    
        $this->assertEquals(['errors' => []], $result);
    }

    public function testValidateClientDataWithInvalidData(): void
    {
        $data = (object)[
            'officeId' => null,
            'serviceId' => null,
            'serviceCounts' => null,
            'startDate' => null,
            'endDate' => null
        ];

        $result = $this->invokePrivateMethod('validateClientData', [$data]);

        $this->assertArrayHasKey('errors', $result);
    }

    public function testGetAvailableDaysListWithValidationErrors(): void
    {
        $queryParams = [
            'officeId' => 'invalid',
            'serviceId' => 'invalid',
            'serviceCount' => '',
            'startDate' => '',
            'endDate' => ''
        ];

        $result = $this->service->getAvailableDaysList($queryParams);

        $this->assertArrayHasKey('errors', $result);
    }

}