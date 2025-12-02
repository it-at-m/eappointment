<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
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
        // Set up mock HTTP client to prevent null errors
        if (!isset(\App::$http)) {
            \App::$http = $this->createMock(\BO\Zmsclient\Http::class);
        }
        $this->servicesListService = new ServicesListService();
    }

    public function testGetServicesListReturnsServiceList(): void
    {
        $expectedServices = new ServiceList();
        
        $this->createMockFacade($expectedServices);

        $result = $this->servicesListService->getServicesList();

        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertEquals($expectedServices, $result);
    }

    public function testGetServicesListReturnsEmptyServiceList(): void
    {
        $expectedServices = new ServiceList();
        
        $this->createMockFacade($expectedServices);

        $result = $this->servicesListService->getServicesList();

        $this->assertInstanceOf(ServiceList::class, $result);
        $this->assertCount(0, $result);
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
                    
                    public static function getServices(): \BO\Zmscitizenapi\Models\Collections\ServiceList|array {
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