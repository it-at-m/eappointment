<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Service\ServiceListByOfficeService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ServiceListByOfficeServiceTest extends TestCase
{
    private ServiceListByOfficeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up mock HTTP client to prevent null errors
        if (!isset(\App::$http)) {
            \App::$http = $this->createMock(\BO\Zmsclient\Http::class);
        }
        $this->service = new ServiceListByOfficeService();
    }

    public function testGetServiceListReturnsServiceList(): void
    {
        // Arrange
        $expectedServices = new ServiceList();
        $officeId = 123;
        $queryParams = ['officeId' => (string)$officeId];

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedServices);

        // Act
        $result = $this->service->getServiceList($queryParams);

        // Assert
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertEquals($expectedServices, $result);
    }

    public function testGetServiceListReturnsEmptyServiceList(): void
    {
        // Arrange
        $expectedServices = new ServiceList();
        $officeId = 123;
        $queryParams = ['officeId' => (string)$officeId];

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedServices);

        // Act
        $result = $this->service->getServiceList($queryParams);

        // Assert
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertCount(0, $result);
    }

    public function testGetServiceListWithMissingOfficeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [];
        $expectedError = ['errors' => ['Invalid office ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getServiceList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetServiceListWithInvalidOfficeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['officeId' => 'invalid'];
        $expectedError = ['errors' => ['Invalid office ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getServiceList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetServiceListWithNonNumericOfficeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['officeId' => 'abc123'];
        $expectedError = ['errors' => ['Invalid office ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getServiceList($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockValidationService(array $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ValidationService';
        if (!\class_exists($class, false)) {
            eval('
                namespace BO\Zmscitizenapi\Services\Core;
                class ValidationService {
                    public static function validateGetServicesByOfficeId(?int $officeId): array {
                        return unserialize(\'' . serialize($returnValue) . '\');
                    }
                }
            ');
        }
    }

    private function createMockFacade(ServiceList $returnValue): void
    {
        $class = 'BO\\Zmscitizenapi\\Services\\Core\\ZmsApiFacadeService';
        if (!\class_exists($class, false)) {
            $serialized = base64_encode(serialize($returnValue));
            eval('
                namespace BO\Zmscitizenapi\Services\Core;
                class ZmsApiFacadeService {
                    private static $mockReturnValue = null;
                    
                    public static function getServicesByOfficeId(int $officeId): \BO\Zmscitizenapi\Models\Collections\ServiceList|array {
                        if (self::$mockReturnValue === null) {
                            self::$mockReturnValue = unserialize(base64_decode(\'' . $serialized . '\'));
                        }
                        return self::$mockReturnValue;
                    }
                }
            ');
        }
    }
}