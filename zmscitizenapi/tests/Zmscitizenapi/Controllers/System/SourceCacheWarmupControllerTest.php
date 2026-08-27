<?php

namespace BO\Zmscitizenapi\Tests\Controllers\System;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class SourceCacheWarmupControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\System\SourceCacheWarmupController";

    private ?string $previousWarmupToken = null;

    private bool $hadWarmupToken = false;

    public function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';
        if (\App::$cache) {
            \App::$cache->clear();
        }

        $existing = getenv('SOURCE_CACHE_WARMUP_TOKEN');
        $this->hadWarmupToken = $existing !== false;
        $this->previousWarmupToken = $this->hadWarmupToken ? (string) $existing : null;
        putenv('SOURCE_CACHE_WARMUP_TOKEN=test-warmup-token');
    }

    public function tearDown(): void
    {
        if ($this->hadWarmupToken) {
            putenv('SOURCE_CACHE_WARMUP_TOKEN=' . $this->previousWarmupToken);
        } else {
            putenv('SOURCE_CACHE_WARMUP_TOKEN');
        }
        parent::tearDown();
    }

    public function testRendering()
    {
        // Inherited Slim base expects 200; warmup requires a token header.
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json")
            ]
        ]);
        $response = $this->render([], [
            '__header' => [
                'X-Source-Cache-Warmup-Token' => 'test-warmup-token',
            ],
        ], [], 'POST');
        $this->assertEquals(200, $response->getStatusCode());
        return $response;
    }

    public function testWarmupRouteIsPostOnly()
    {
        $routing = file_get_contents(dirname(__DIR__, 3) . '/../routing.php');
        $this->assertNotFalse($routing);
        $this->assertMatchesRegularExpression(
            "/\\\\App::\\\$slim->post\\(\\s*'\\/source-cache\\/warmup\\/'/s",
            $routing
        );
        $this->assertDoesNotMatchRegularExpression(
            "/\\['GET',\\s*'POST'\\],\\s*'\\/source-cache\\/warmup\\/'/s",
            $routing
        );
    }

    public function testWarmupDisabledWithoutToken()
    {
        putenv('SOURCE_CACHE_WARMUP_TOKEN');
        $response = $this->render([], [], [], 'POST');
        $this->assertEquals(ErrorMessages::get('notFound')['statusCode'], $response->getStatusCode());
    }

    public function testWarmupUnauthorizedWithoutHeader()
    {
        $response = $this->render([], [], [], 'POST');
        $this->assertEquals(ErrorMessages::get('unauthorized')['statusCode'], $response->getStatusCode());
    }

    public function testWarmupRebuildsOfficesAndServicesCache()
    {
        \App::$cache->set('processed_offices_and_services', ['stale' => true], 3600);
        \App::$cache->set('source_unittest', ['stale' => true], 3600);

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json")
            ]
        ]);

        $response = $this->render([], [
            '__header' => [
                'X-Source-Cache-Warmup-Token' => 'test-warmup-token',
            ],
        ], [], 'POST');

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertTrue($body['warmed']);
        $this->assertContains('processed_offices_and_services', $body['refreshedKeys']);
        $this->assertContains('source_unittest', $body['refreshedKeys']);
        $this->assertArrayNotHasKey('deletedKeys', $body);
        $this->assertNotEquals(['stale' => true], \App::$cache->get('processed_offices_and_services'));
        $this->assertNotEquals(['stale' => true], \App::$cache->get('source_unittest'));
    }

    public function testWarmupDoesNotDropPerOfficeKeys()
    {
        $sourceJson = json_decode($this->readFixture("GET_SourceGet_dldb.json"), true);
        $source = new \BO\Zmsentities\Source($sourceJson['data']);
        \App::$cache->set('source_unittest', $source, 3600);
        \App::$cache->set('processed_services_by_office_9999998', ['stale' => true], 3600);
        \App::$cache->set('processed_services_by_office_9999998_unpublished', ['stale' => true], 3600);

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json")
            ]
        ]);

        $response = $this->render([], [
            '__header' => [
                'X-Source-Cache-Warmup-Token' => 'test-warmup-token',
            ],
        ], [], 'POST');

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertTrue($body['warmed']);
        $this->assertNotContains('processed_services_by_office_9999998', $body['refreshedKeys']);
        $this->assertSame(['stale' => true], \App::$cache->get('processed_services_by_office_9999998'));
        $this->assertSame(['stale' => true], \App::$cache->get('processed_services_by_office_9999998_unpublished'));
    }
}
