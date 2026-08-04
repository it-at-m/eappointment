<?php

namespace BO\Zmscitizenapi\Tests\Controllers\System;

use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class SourceCacheWarmupControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\System\SourceCacheWarmupController";

    public function setUp(): void
    {
        parent::setUp();
        \App::$source_name = 'unittest';
        if (\App::$cache) {
            \App::$cache->clear();
        }
        putenv('SOURCE_CACHE_WARMUP_TOKEN=test-warmup-token');
    }

    public function tearDown(): void
    {
        putenv('SOURCE_CACHE_WARMUP_TOKEN');
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
        $this->assertContains('processed_offices_and_services', $body['deletedKeys']);
        $this->assertContains('source_unittest', $body['deletedKeys']);
        $this->assertNotEquals(['stale' => true], \App::$cache->get('processed_offices_and_services'));
    }
}
