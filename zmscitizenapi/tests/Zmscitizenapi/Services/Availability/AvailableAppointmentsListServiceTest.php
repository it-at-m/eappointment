<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Availability\AvailableAppointmentsListService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AvailableAppointmentsListServiceTest extends TestCase
{
    private AvailableAppointmentsListService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailableAppointmentsListService();
    }

    public function testGetAvailableAppointmentsListReturnsAvailableAppointments(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '123',
            'serviceId' => '456,789',
            'serviceCount' => '1,2'
        ];
        
        $expectedAppointments = new AvailableAppointments([1705317600, 1705321200]); // Example timestamps
        
        $this->createMockValidationService([]);
        $this->createMockFacade($expectedAppointments);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertInstanceOf(AvailableAppointments::class, $result);
        $this->assertEquals($expectedAppointments, $result);
    }

    public function testGetAvailableAppointmentsListReturnsEmptyAppointments(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => '1'
        ];
        
        $expectedAppointments = new AvailableAppointments([]);
        
        $this->createMockValidationService([]);
        $this->createMockFacade($expectedAppointments);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertInstanceOf(AvailableAppointments::class, $result);
        $this->assertEmpty($result->toArray()['appointmentTimestamps']);
    }

    public function testGetAvailableAppointmentsListWithMissingParametersReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [];
        $expectedError = ['errors' => ['Required parameters missing']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableAppointmentsListWithInvalidDateReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'date' => 'invalid-date',
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => '1'
        ];
        $expectedError = ['errors' => ['Invalid date format']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableAppointmentsListWithInvalidServiceIdsReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '123',
            'serviceId' => 'invalid,ids',
            'serviceCount' => '1,1'
        ];
        $expectedError = ['errors' => ['Invalid service IDs']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableAppointmentsListWithMismatchedCountsReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '123',
            'serviceId' => '456,789',
            'serviceCount' => '1'  // Only one count for two services
        ];
        $expectedError = ['errors' => ['Service counts must match service IDs']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockValidationService(array $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ValidationService {
                public static function validateGetAvailableAppointments(
                    ?string $date,
                    ?array $officeIds,
                    array $serviceIds,
                    array $serviceCounts
                ): array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }

    private function createMockFacade(AvailableAppointments $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getAvailableAppointments(
                    ?string $date,
                    ?int $officeId,
                    ?array $serviceIds,
                    ?array $serviceCounts
                ): \BO\Zmscitizenapi\Models\AvailableAppointments|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}