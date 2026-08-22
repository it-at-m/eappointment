<?php
declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Services\Office;

use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Collections\ServiceList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenbackend\Services\Office\OfficesServicesRelationsService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OfficesServicesRelationsServiceTest extends TestCase
{
    private OfficesServicesRelationsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfficesServicesRelationsService();
    }

    public function testGetServicesAndOfficesListReturnsOfficeServiceAndRelationList(): void
    {
        // Arrange
        $offices = new OfficeList();
        $services = new ServiceList();
        $relations = new OfficeServiceRelationList();
        $expectedList = new OfficeServiceAndRelationList($offices, $services, $relations);
        
        $this->createMockFacade($expectedList);

        // Act
        $result = $this->service->getServicesAndOfficesList();

        // Assert
        $this->assertInstanceOf(OfficeServiceAndRelationList::class, $result);
        $this->assertEquals($expectedList, $result);
        
        // Verify the structure
        $resultArray = $result->toArray();
        $this->assertArrayHasKey('offices', $resultArray);
        $this->assertArrayHasKey('services', $resultArray);
        $this->assertArrayHasKey('relations', $resultArray);
    }

    public function testGetServicesAndOfficesListReturnsEmptyList(): void
    {
        // Arrange
        $offices = new OfficeList();
        $services = new ServiceList();
        $relations = new OfficeServiceRelationList();
        $expectedList = new OfficeServiceAndRelationList($offices, $services, $relations);
        
        $this->createMockFacade($expectedList);

        // Act
        $result = $this->service->getServicesAndOfficesList();

        // Assert
        $this->assertInstanceOf(OfficeServiceAndRelationList::class, $result);
        
        $resultArray = $result->toArray();
        $this->assertEmpty($resultArray['offices']);
        $this->assertEmpty($resultArray['services']);
        $this->assertEmpty($resultArray['relations']);
    }

    private function createMockFacade(OfficeServiceAndRelationList $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenbackend\Services\Core;
            class ZmsApiFacadeService {
                public static function getServicesAndOffices(): \BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}