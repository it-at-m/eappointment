<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Office\OfficesListService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OfficesListServiceTest extends TestCase
{
    private OfficesListService $officesListService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->officesListService = new OfficesListService();
    }

    public function testGetOfficesListReturnsOfficeList(): void
    {
        $expectedOffices = new OfficeList();
        
        $this->createMockFacade($expectedOffices);

        $result = $this->officesListService->getOfficesList();

        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertEquals($expectedOffices, $result);
    }

    public function testGetOfficesListReturnsEmptyOfficeList(): void
    {
        $expectedOffices = new OfficeList();
        
        $this->createMockFacade($expectedOffices);

        $result = $this->officesListService->getOfficesList();

        $this->assertInstanceOf(OfficeList::class, $result);
        $this->assertCount(0, $result);
    }

    private function createMockFacade(OfficeList $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getOffices(): \BO\Zmscitizenapi\Models\Collections\OfficeList|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}