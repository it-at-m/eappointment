<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\Scope\ScopeByIdService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ScopeByIdServiceTest extends TestCase
{
    private ScopeByIdService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScopeByIdService();
    }

    public function testGetScopeReturnsThinnedScope(): void
    {
        // Arrange
        $expectedScope = $this->createMockThinnedScope();
        $scopeId = 123;
        $queryParams = ['scopeId' => (string)$scopeId];

        $this->createMockValidationService([]);
        $this->createMockFacade($expectedScope);

        // Act
        $result = $this->service->getScope($queryParams);

        // Assert
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals($expectedScope, $result);
    }

    public function testGetScopeWithMissingScopeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [];
        $expectedError = ['errors' => ['Invalid scope ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getScope($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetScopeWithInvalidScopeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['scopeId' => 'invalid'];
        $expectedError = ['errors' => ['Invalid scope ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getScope($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetScopeWithNonNumericScopeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = ['scopeId' => 'abc123'];
        $expectedError = ['errors' => ['Invalid scope ID']];
        
        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getScope($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    private function createMockThinnedScope(): ThinnedScope
    {
        return new ThinnedScope(
            id: 123,
            provider: null,
            shortName: 'Test Scope',
            emailRequired: false,
            telephoneActivated: false,
            telephoneRequired: false,
            customTextfieldActivated: false,
            customTextfieldRequired: false,
            customTextfieldLabel: null,
            captchaActivatedRequired: false,
            displayInfo: null
        );
    }

    private function createMockValidationService(array $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ValidationService {
                public static function validateGetScopeById(?int $scopeId): array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }

    private function createMockFacade(ThinnedScope $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ZmsApiFacadeService {
                public static function getScopeById(?int $scopeId): \BO\Zmscitizenapi\Models\ThinnedScope|array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }
}