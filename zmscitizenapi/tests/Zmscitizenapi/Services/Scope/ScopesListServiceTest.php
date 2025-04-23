<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Scope;

use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\Scope\ScopesListService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ScopesListServiceTest extends TestCase
{
    private ScopesListService $scopesListService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scopesListService = new ScopesListService();
    }

    public function testGetScopesListReturnsThinnedScopeList(): void
    {
        // Arrange
        $expectedScopes = new ThinnedScopeList();
        
        $this->createMockFacade($expectedScopes);

        // Act
        $result = $this->scopesListService->getScopesList();

        // Assert
        $this->assertInstanceOf(ThinnedScopeList::class, $result);
        $this->assertEquals($expectedScopes, $result);
    }

    public function testGetScopesListReturnsEmptyThinnedScopeList(): void
    {
        // Arrange
        $expectedScopes = new ThinnedScopeList();
        
        $this->createMockFacade($expectedScopes);

        // Act
        $result = $this->scopesListService->getScopesList();

        // Assert
        $this->assertInstanceOf(ThinnedScopeList::class, $result);
        $this->assertCount(0, $result);
    }

    private function createMockFacade(ThinnedScopeList $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getScopes(): \BO\Zmscitizenapi\Models\Collections\ThinnedScopeList|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}