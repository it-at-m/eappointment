<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ScopeByTimeslotServiceTest extends TestCase
{
    private ScopeByTimeslotService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScopeByTimeslotService();
    }

    public function testGetScopeByTimeslotReturnsThinnedScope(): void
    {
        // Arrange
        $timestamp = time() + 3600;
        $scopeId = 45;

        $queryParams = [
            'officeId' => '10489',
            'timestamp' => (string) $timestamp,
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ];

        $expectedScope = $this->createMockThinnedScope($scopeId);
        $processList = $this->createMockProcessList($timestamp, $scopeId);

        $this->createMockValidationService([]);
        $this->createMockFacade($processList, $expectedScope);

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals($expectedScope, $result);
    }

    public function testGetScopeByTimeslotWithMissingOfficeIdReturnsValidationError(): void
    {
        // Arrange
        $queryParams = [
            'timestamp' => (string) (time() + 3600),
            'serviceId' => '1063475',
            'serviceCount' => '1',
        ];

        $expectedError = ['errors' => ['Invalid office ID']];

        $this->createMockValidationService($expectedError);

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetScopeByTimeslotWithoutMatchingProcessReturnsScopesNotFoundError(): void
    {
        // Arrange
        $timestamp = time() + 3600;

        $queryParams = [
            'officeId' => '10489',
            'timestamp' => (string) $timestamp,
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ];

        $emptyProcessList = new ProcessList();

        $this->createMockValidationService([]);
        $this->createMockFacade($emptyProcessList, []);

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertSame('scopesNotFound', $result['errors'][0]['errorCode']);
    }

    public function testGetScopeByTimeslotWithoutScopeIdOnProcessReturnsScopeNotFoundError(): void
    {
        // Arrange
        $timestamp = time() + 3600;

        $queryParams = [
            'officeId' => '10489',
            'timestamp' => (string) $timestamp,
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ];

        $processList = $this->createMockProcessList($timestamp, null);

        $this->createMockValidationService([]);
        $this->createMockFacade($processList, []);

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertSame('scopeNotFound', $result['errors'][0]['errorCode']);
    }

    private function createMockThinnedScope(int $scopeId): ThinnedScope
    {
        return new ThinnedScope(
            id: $scopeId,
            provider: null,
            shortName: 'WB 04',
            emailRequired: false,
            telephoneActivated: false,
            telephoneRequired: false,
            customTextfieldActivated: false,
            customTextfieldRequired: false,
            customTextfieldLabel: null,
            captchaActivatedRequired: false,
            infoForAppointment: 'WB04',
            infoForAllAppointments: 'Hey there WB04',
            slotsPerAppointment: null
        );
    }

    private function createMockProcessList(int $timestamp, ?int $scopeId): ProcessList
    {
        $process = new Process();

        $appointment = new \stdClass();
        $appointment->date = $timestamp;

        $process->appointments = [$appointment];

        if ($scopeId !== null) {
            $scope = new Scope();
            $scope->id = $scopeId;
            $process->scope = $scope;
        } else {
            $process->scope = null;
        }

        $processList = new ProcessList();
        $processList->addEntity($process);

        return $processList;
    }

    private function createMockValidationService(array $returnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;
            class ValidationService {
                public static function validateGetScopeByTimeslot(
                    ?int $officeId,
                    ?int $timestamp,
                    ?array $serviceIds,
                    ?array $serviceCounts
                ): array {
                    return unserialize(\'' . serialize($returnValue) . '\');
                }
            }
        ');
    }

    private function createMockFacade(ProcessList $freeAppointmentsReturnValue, ThinnedScope|array $scopeReturnValue): void
    {
        eval('
            namespace BO\Zmscitizenapi\Services\Core;

            class ZmsApiFacadeService {
                public static function getFreeAppointments(
                    int $officeId,
                    array $serviceIds,
                    array $serviceCounts,
                    array $date
                ): \BO\Zmsentities\Collection\ProcessList|array {
                    return unserialize(\'' . serialize($freeAppointmentsReturnValue) . '\');
                }

                public static function getScopeById(?int $scopeId): \BO\Zmscitizenapi\Models\ThinnedScope|array {
                    return unserialize(\'' . serialize($scopeReturnValue) . '\');
                }
            }
        ');
    }
}
