<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Scope;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

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

        $this->createMockScopeByTimeslotServiceClass();
        \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$lastQueryParams = [];
        \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$returnValue = null;

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

        \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$returnValue = $expectedScope;

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
        ], \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$lastQueryParams);
    }

    public function testScopeNotFound(): void
    {
        $expectedResponse = [
            'errors' => [[
                'errorCode' => 'scopeNotFound',
                'errorMessage' => 'Scope could not be resolved.',
                'statusCode' => 404,
                'errorType' => 'error',
            ]]
        ];

        \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$returnValue = $expectedResponse;

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

        \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$returnValue = $expectedScope;

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
        ], \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$lastQueryParams);

        $this->assertArrayNotHasKey(
            '/scope-by-timeslot',
            \BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::$lastQueryParams
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

    private function createMockScopeByTimeslotServiceClass(): void
    {
        if (class_exists(\BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService::class, false)) {
            return;
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
    }
}
