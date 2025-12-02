<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Availability;

use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Availability\AvailableDaysListService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AvailableDaysListServiceTest extends TestCase
{
    private AvailableDaysListService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up mock HTTP client to prevent null errors
        if (!isset(\App::$http)) {
            \App::$http = $this->createMock(\BO\Zmsclient\Http::class);
        }
    }

    public function testGetAvailableDaysListReturnsAvailableDays(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => '1,2',
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];

        $expectedDays = new AvailableDays(['2025-01-15', '2025-01-16']);

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedDays);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertInstanceOf(AvailableDays::class, $result);
        $this->assertEquals($expectedDays, $result);
    }

    public function testGetAvailableDaysListReturnsEmptyAvailableDays(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => '1',
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];

        $expectedDays = new AvailableDays([]);

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedDays);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertInstanceOf(AvailableDays::class, $result);
        $this->assertEmpty($result->toArray()['availableDays']);
    }

    public function testGetAvailableDaysListWithMissingParametersReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [];
        $expectedError = ['errors' => ['Required parameters missing']];

        $this->createMockValidationService($expectedError);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableDaysListWithInvalidOfficeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => 'invalid',
            'serviceId' => '456',
            'serviceCount' => '1',
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];
        $expectedError = ['errors' => ['Invalid office ID']];

        $this->createMockValidationService($expectedError);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableDaysListWithInvalidDateRangeReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => '1',
            'startDate' => 'invalid',
            'endDate' => '2025-01-31'
        ];
        $expectedError = ['errors' => ['Invalid date range']];

        $this->createMockValidationService($expectedError);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableDaysListWithInvalidServiceCountReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => '123',
            'serviceId' => '456',
            'serviceCount' => 'invalid,counts',
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];
        $expectedError = ['errors' => ['Invalid service count']];

        $this->createMockValidationService($expectedError);
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetAvailableDaysListWithInvalidServiceLocationCombinationReturnsError(): void
    {
        // Arrange
        $queryParams = [
            'officeId' => '999',  // This office ID will trigger the validation error
            'serviceId' => '456',
            'serviceCount' => '1',
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];
        $expectedError = ['errors' => [['errorCode' => 'invalidLocationAndServiceCombination']]];

        $this->createMockValidationService([]);  // Initial validation passes
        $this->service = new AvailableDaysListService();

        // Act
        $result = $this->service->getAvailableDaysList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockValidationService(array $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ValidationService';
        if (!\class_exists($class, false)) {
            eval('namespace BO\\Zmscitizenapi\\Services\\Core;
                class ValidationService {
                    public static function validateGetBookableFreeDays(
                        ?array $officeIds,
                        ?array $serviceIds,
                        ?string $startDate,
                        ?string $endDate,
                        array $serviceCounts
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

    private function createMockFacade(AvailableDays $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ZmsApiFacadeService';
        if (!\class_exists($class, false)) {
            eval('namespace BO\\Zmscitizenapi\\Services\\Core;
                class ZmsApiFacadeService {
                    public static function getBookableFreeDays(
                        array $officeIds,
                        array $serviceIds,
                        array $serviceCounts,
                        string $startDate,
                        string $endDate
                    ): \\BO\\Zmscitizenapi\\Models\\AvailableDays|array {
                        return unserialize(\'' . serialize($returnValue) . '\');
                    }
                }'
            );
        }
    }
}
