<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Scope;

use BO\Zmscitizenapi\Models\ThinnedProvider;
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

        $this->createMockValidationServiceClass();
        $this->createMockFacadeClass();

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue = new ProcessList();
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$scopeReturnValues = [];

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

        $expectedScope = $this->createMockThinnedScope(
            $scopeId,
            'dldb',
            'WB04',
            'Hey there WB04'
        );

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue =
            $this->createProcessList([
                ['timestamp' => $timestamp, 'scopeId' => $scopeId],
            ]);
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$scopeReturnValues = [
            $scopeId => $expectedScope,
        ];

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

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = $expectedError;

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

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue = new ProcessList();

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertSame('scopesNotFound', $result['errors'][0]['errorCode']);
    }

    public function testGetScopeByTimeslotWithoutUsableScopeReturnsScopeNotFoundError(): void
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

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue =
            $this->createProcessList([
                ['timestamp' => $timestamp, 'scopeId' => null],
            ]);

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertSame('scopeNotFound', $result['errors'][0]['errorCode']);
    }

    public function testGetScopeByTimeslotPropagatesUpstreamFreeAppointmentsError(): void
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

        $expectedError = [
            'errors' => [[
                'errorCode' => 'appointmentNotAvailable',
                'errorMessage' => 'Appointment not available.',
                'statusCode' => 500,
                'errorType' => 'error',
            ]]
        ];

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue = $expectedError;

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedError, $result);
    }

    public function testGetScopeByTimeslotContinuesScanningUntilUsableScopeIsFound(): void
    {
        // Arrange
        $timestamp = time() + 3600;
        $firstScopeId = 36;
        $secondScopeId = 45;

        $queryParams = [
            'officeId' => '10489',
            'timestamp' => (string) $timestamp,
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ];

        $wrongSourceScope = $this->createMockThinnedScope(
            $firstScopeId,
            'zms',
            'WB03',
            'Hey there WB03'
        );
        $expectedScope = $this->createMockThinnedScope(
            $secondScopeId,
            'dldb',
            'WB04',
            'Hey there WB04'
        );

        \BO\Zmscitizenapi\Services\Core\ValidationService::$returnValue = [];
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$freeAppointmentsReturnValue =
            $this->createProcessList([
                ['timestamp' => $timestamp, 'scopeId' => $firstScopeId],
                ['timestamp' => $timestamp, 'scopeId' => $secondScopeId],
            ]);
        \BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::$scopeReturnValues = [
            $firstScopeId => $wrongSourceScope,
            $secondScopeId => $expectedScope,
        ];

        // Act
        $result = $this->service->getScopeByTimeslot($queryParams);

        // Assert
        $this->assertInstanceOf(ThinnedScope::class, $result);
        $this->assertEquals($expectedScope, $result);
        $this->assertSame('WB04', $result->infoForAppointment);
    }

    private function createMockThinnedScope(
        int $scopeId,
        string $providerSource = 'dldb',
        string $infoForAppointment = 'WB04',
        string $infoForAllAppointments = 'Hey there WB04'
    ): ThinnedScope {
        $provider = new ThinnedProvider(
            id: 10489,
            name: 'Bürgerbüro Ruppertstraße',
            displayName: null,
            lat: null,
            lon: null,
            source: $providerSource,
            contact: null
        );

        return new ThinnedScope(
            id: $scopeId,
            provider: $provider,
            shortName: 'WB 04',
            emailRequired: false,
            telephoneActivated: false,
            telephoneRequired: false,
            customTextfieldActivated: false,
            customTextfieldRequired: false,
            customTextfieldLabel: null,
            captchaActivatedRequired: false,
            infoForAppointment: $infoForAppointment,
            infoForAllAppointments: $infoForAllAppointments,
            slotsPerAppointment: null
        );
    }

    private function createProcessList(array $processSpecs): ProcessList
    {
        $processList = new ProcessList();

        foreach ($processSpecs as $spec) {
            $process = new Process();

            $appointment = new \stdClass();
            $appointment->date = $spec['timestamp'];

            $process->appointments = [$appointment];

            if (array_key_exists('scopeId', $spec) && $spec['scopeId'] !== null) {
                $scope = new Scope();
                $scope->id = $spec['scopeId'];
                $process->scope = $scope;
            } else {
                $process->scope = null;
            }

            $processList->addEntity($process);
        }

        return $processList;
    }

    private function createMockValidationServiceClass(): void
    {
        if (class_exists(\BO\Zmscitizenapi\Services\Core\ValidationService::class, false)) {
            return;
        }

        eval('
            namespace BO\Zmscitizenapi\Services\Core;

            class ValidationService
            {
                public static array $returnValue = [];

                public static function validatePostAppointmentReserve(
                    ?int $officeId,
                    ?array $serviceIds,
                    ?array $serviceCounts,
                    ?int $timestamp,
                    ?bool $captchaRequired = false,
                    ?string $captchaToken = null,
                    ?object $tokenValidator = null
                ): array {
                    return unserialize(serialize(self::$returnValue));
                }
            }
        ');
    }

    private function createMockFacadeClass(): void
    {
        if (class_exists(\BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService::class, false)) {
            return;
        }

        eval('
            namespace BO\Zmscitizenapi\Services\Core;

            class ZmsApiFacadeService
            {
                public static $freeAppointmentsReturnValue;
                public static array $scopeReturnValues = [];

                public static function getFreeAppointments(
                    int $officeId,
                    array $serviceIds,
                    array $serviceCounts,
                    array $date
                ): \BO\Zmsentities\Collection\ProcessList|array {
                    return unserialize(serialize(self::$freeAppointmentsReturnValue));
                }

                public static function getScopeById(?int $scopeId): \BO\Zmscitizenapi\Models\ThinnedScope|array {
                    if ($scopeId !== null && array_key_exists($scopeId, self::$scopeReturnValues)) {
                        return unserialize(serialize(self::$scopeReturnValues[$scopeId]));
                    }

                    return [];
                }
            }
        ');
    }
}
