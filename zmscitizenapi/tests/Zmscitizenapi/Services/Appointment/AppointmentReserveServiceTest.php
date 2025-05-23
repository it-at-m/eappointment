<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Appointment;

use BO\Zmscitizenapi\Services\Appointment\AppointmentReserveService;
use BO\Zmscitizenapi\Tests\MiddlewareTestCase;

class AppointmentReserveServiceTest extends MiddlewareTestCase
{
    private AppointmentReserveService $service;
    private \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentReserveService();
        $this->reflector = new \ReflectionClass(AppointmentReserveService::class);
        \App::$CAPTCHA_ENABLED = false;
    }

    protected function tearDown(): void
    {
        \App::$CAPTCHA_ENABLED = false;
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
            'officeId' => '12345',
            'serviceId' => ['1', '2'],
            'serviceCount' => [1, 2],
            'timestamp' => '1704114000'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertEquals(12345, $result->officeId);
        $this->assertEquals(['1', '2'], $result->serviceIds);
        $this->assertEquals([1, 2], $result->serviceCounts);
        $this->assertEquals(1704114000, $result->timestamp);
    }

    public function testExtractClientDataWithInvalidInput(): void
    {
        $body = [
            'officeId' => 'invalid',
            'timestamp' => 'invalid'
        ];

        $result = $this->invokePrivateMethod('extractClientData', [$body]);

        $this->assertNull($result->officeId);
        $this->assertNull($result->serviceIds);
        $this->assertEquals([1], $result->serviceCounts);
        $this->assertNull($result->timestamp);
    }

    public function testProcessReservationWithValidationErrors(): void
    {
        $body = [
            'officeId' => 'invalid',
            'serviceId' => null,
            'timestamp' => null
        ];

        $result = $this->service->processReservation($body);

        $this->assertArrayHasKey('errors', $result);
    }

}