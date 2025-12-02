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
        // Set up mock HTTP client to prevent null errors
        if (!isset(\App::$http)) {
            \App::$http = $this->createMock(\BO\Zmsclient\Http::class);
        }
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
        $this->service = new AvailableAppointmentsListService();

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
        $this->service = new AvailableAppointmentsListService();

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
        $this->service = new AvailableAppointmentsListService();

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
        $this->service = new AvailableAppointmentsListService();

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
        $this->service = new AvailableAppointmentsListService();

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
        $this->service = new AvailableAppointmentsListService();

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableAppointmentsListWithInvalidServiceLocationCombinationReturnsError(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '999',  // This office ID will trigger the validation error
            'serviceId' => '456',
            'serviceCount' => '1'
        ];
        $expectedError = ['errors' => [['errorCode' => 'invalidLocationAndServiceCombination']]];

        $this->createMockValidationService([]);  // Initial validation passes
        $this->service = new AvailableAppointmentsListService();

        // Act
        $result = $this->service->getAvailableAppointmentsList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableAppointmentsListByOfficeWithInvalidServiceLocationCombinationReturnsError(): void
    {
        // Arrange
        $queryParams = [
            'date' => '2025-01-15',
            'officeId' => '999',  // This office ID will trigger the validation error
            'serviceId' => '456',
            'serviceCount' => '1'
        ];
        $expectedError = ['errors' => [['errorCode' => 'invalidLocationAndServiceCombination']]];

        $this->createMockValidationService([]);  // Initial validation passes
        $this->service = new AvailableAppointmentsListService();

        // Act
        $result = $this->service->getAvailableAppointmentsListByOffice($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockValidationService(array $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ValidationService';
        if (!\class_exists($class, false)) {
            eval(
                'namespace BO\\Zmscitizenapi\\Services\\Core; class ValidationService {
                    public static function validateGetAvailableAppointments(
                        ?string $date,
                        ?array $officeIds,
                        array $serviceIds,
                        array $serviceCounts,
                        ?bool $captchaRequired = null,
                        ?string $captchaToken = null,
                        $tokenValidator = null
                    ): array {
                            return unserialize(\'' . serialize($returnValue) . '\');
                    }

                    public static function validateServiceLocationCombination(
                        int $officeId,
                        array $serviceIds
                    ): array {
                        if ($officeId === 999) {
                            return ["errors" => [["errorCode" => "invalidLocationAndServiceCombination"]]];
                        }
                        return [];
                    }
                }'
            );
        }
    }

    private function createMockFacade(AvailableAppointments $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ZmsApiFacadeService';
        if (!\class_exists($class, false)) {
            eval(
                'namespace BO\\Zmscitizenapi\\Services\\Core; class ZmsApiFacadeService {
                    public static function getAvailableAppointments(
                        ?string $date,
                        ?array $officeIds,
                        ?array $serviceIds,
                        ?array $serviceCounts, ?bool $groupByOffice = false
                    ): \\BO\\Zmscitizenapi\\Models\\AvailableAppointments|array {
                        return unserialize(\'' . serialize($returnValue) . '\'); }
                    public function getScopeByOfficeId(int $officeId) {
                        return (object)["captchaActivatedRequired" => false];
                    }
                }'
            );
        }
    }
}