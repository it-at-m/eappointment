<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Office\OfficeListByServiceService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OfficeListByServiceServiceTest extends TestCase
{
    private OfficeListByServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfficeListByServiceService();
    }

    public function testGetOfficeListReturnsOfficeList(): void
    {
        // Arrange
        $expectedOffices = new OfficeList();
        $serviceId = 123;
        $queryParams = ['serviceId' => (string)$serviceId];

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedOffices);

        // Act
        $result = $this->service->getOfficeList($queryParams);

        // Assert
        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertEquals($expectedOffices, $result);
    }

    public function testGetOfficeListReturnsEmptyOfficeList(): void
    {
        // Arrange
        $expectedOffices = new OfficeList();
        $serviceId = 123;
        $queryParams = ['serviceId' => (string)$serviceId];

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedOffices);

        // Act
        $result = $this->service->getOfficeList($queryParams);

        // Assert
        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertCount(0, $result);
    }

    public function testGetOfficeListWithMissingServiceIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [];
        $expectedError = ['errors' => ['Invalid service ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getOfficeList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetOfficeListWithInvalidServiceIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['serviceId' => 'invalid'];
        $expectedError = ['errors' => ['Invalid service ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getOfficeList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetOfficeListWithNonNumericServiceIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['serviceId' => 'abc123'];
        $expectedError = ['errors' => ['Invalid service ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getOfficeList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockValidationService(array $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ValidationService {
                public static function validateGetOfficeListByServiceId(?int $serviceId): array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }

    private function createMockFacade(OfficeList $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getOfficeListByServiceId(int $serviceId): \BO\Zmscitizenapi\Models\Collections\OfficeList|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}