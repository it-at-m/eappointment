<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Tests\ControllerTestCase;
use BO\Zmscitizenapi\Utils\ErrorMessages;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ScopeByTimeslotControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Scope\ScopeByTimeslotController";

    private ?string $originalRequestUri = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalRequestUri = $_SERVER['REQUEST_URI'] ?? null;

        if (!$this->createMockScopeByTimeslotServiceClass()) {
            $this->markTestSkipped(
                'ScopeByTimeslotService was already loaded and cannot be replaced by the test mock.'
            );
        }

        $this->resetMockScopeByTimeslotServiceState();

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    public function tearDown(): void
    {
        if ($this->originalRequestUri !== null) {
            $_SERVER['REQUEST_URI'] = $this->originalRequestUri;
        } else {
            unset($_SERVER['REQUEST_URI']);
        }

        parent::tearDown();
    }

    public function testRendering(): void
    {
        $expectedScope = $this->createMockThinnedScope();

        $this->setMockScopeByTimeslotServiceReturnValue($expectedScope);

        $response = $this->render([], [
            'officeId' => '10489',
            'timestamp' => '1774328700',
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ], []);

        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedScope->toArray(), $responseBody);
        $this->assertEquals([
            'officeId' => '10489',
            'timestamp' => '1774328700',
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ], $this->getMockScopeByTimeslotServiceLastQueryParams());
    }

    public function testScopeNotFound(): void
    {
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('scopeNotFound')
            ]
        ];

        $this->setMockScopeByTimeslotServiceReturnValue($expectedResponse);

        $response = $this->render([], [
            'officeId' => '10489',
            'timestamp' => '1774328700',
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ], []);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            $expectedResponse,
            json_decode((string) $response->getBody(), true)
        );
    }

    public function testRenderingWithNormalizedQueryParamsFromRequestUri(): void
    {
        $expectedScope = $this->createMockThinnedScope();

        $this->setMockScopeByTimeslotServiceReturnValue($expectedScope);

        $_SERVER['REQUEST_URI'] = '/terminvereinbarung/api/citizen/scope-by-timeslot?/scope-by-timeslot&officeId=10489&timestamp=1774328700&serviceId=1063475&serviceCount=1&source=dldb';

        $response = $this->render([], [], []);
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedScope->toArray(), $responseBody);

        $this->assertEquals([
            'officeId' => '10489',
            'timestamp' => '1774328700',
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ], $this->getMockScopeByTimeslotServiceLastQueryParams());

        $this->assertArrayNotHasKey(
            '/scope-by-timeslot',
            $this->getMockScopeByTimeslotServiceLastQueryParams()
        );
    }

    public function testUpstreamErrorIsReturnedWithOriginalStatusCode(): void
    {
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('appointmentNotAvailable')
            ]
        ];

        $this->setMockScopeByTimeslotServiceReturnValue($expectedResponse);

        $response = $this->render([], [
            'officeId' => '10489',
            'timestamp' => '1774328700',
            'serviceId' => '1063475',
            'serviceCount' => '1',
            'source' => 'dldb',
        ], []);

        $this->assertEquals(
            ErrorMessages::get('appointmentNotAvailable')['statusCode'],
            $response->getStatusCode()
        );
        $this->assertEqualsCanonicalizing(
            $expectedResponse,
            json_decode((string) $response->getBody(), true)
        );
    }

    private function createMockThinnedScope(): ThinnedScope
    {
        return new ThinnedScope(
            id: 45,
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

    private function resetMockScopeByTimeslotServiceState(): void
    {
        $className = \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::class;

        if (property_exists($className, 'lastQueryParams')) {
            $className::$lastQueryParams = [];
        }

        if (property_exists($className, 'returnValue')) {
            $className::$returnValue = null;
        }
    }

    private function setMockScopeByTimeslotServiceReturnValue(mixed $returnValue): void
    {
        $className = \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::class;

        if (!property_exists($className, 'returnValue')) {
            $this->fail('Mocked ScopeByTimeslotService does not expose static property $returnValue.');
        }

        $className::$returnValue = $returnValue;
    }

    private function getMockScopeByTimeslotServiceLastQueryParams(): array
    {
        $className = \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::class;

        if (!property_exists($className, 'lastQueryParams')) {
            $this->fail('Mocked ScopeByTimeslotService does not expose static property $lastQueryParams.');
        }

        return $className::$lastQueryParams;
    }

    private function createMockScopeByTimeslotServiceClass(): bool
    {
        $className = \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::class;

        if (class_exists($className, false)) {
            return property_exists($className, 'returnValue')
                && property_exists($className, 'lastQueryParams');
        }

        eval('
            namespace BO\Zmscitizenapi\Services\Scope;

            class ScopeByTimeslotService
            {
                public static $returnValue;
                public static array $lastQueryParams = [];

                public function getScopeByTimeslot(array $queryParams): \BO\Zmscitizenapi\Models\ThinnedScope|array
                {
                    self::$lastQueryParams = $queryParams;
                    return self::$returnValue;
                }
            }
        ');

        return true;
    }
}
