<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Service\ServicesListService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ServicesListServiceTest extends TestCase
{
    private ServicesListService $servicesListService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicesListService = new ServicesListService();
    }

    public function testGetServicesListReturnsServiceList(): void
    {
        // Arrange
        $expectedServices = new ServiceList();
        
        $this->createMockFacade($expectedServices);

        // Act
        $result = $this->servicesListService->getServicesList();

        // Assert
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertEquals($expectedServices, $result);
    }

    public function testGetServicesListReturnsEmptyServiceList(): void
    {
        // Arrange
        $expectedServices = new ServiceList();
        
        $this->createMockFacade($expectedServices);

        // Act
        $result = $this->servicesListService->getServicesList();

        // Assert
        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertCount(0, $result);
    }

    private function createMockFacade(ServiceList $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getServices(): \BO\Zmscitizenapi\Models\Collections\ServiceList|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}