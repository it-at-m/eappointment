<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Service;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class ServicesListControllerTest extends ControllerTestCase
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Service\ServicesListController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
            // Clear specific cache keys that might affect this test
            \App::$cache->delete('processed_services');
            \App::$cache->delete('processed_services_unpublished');
        }

        // Ensure headers are set to prevent unpublished access
        $_SERVER['HTTP_HOST'] = 'test.example.com';
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
    }

    public function tearDown(): void
    {
        unset($_SERVER['HTTP_HOST']);
        parent::tearDown();
    }

    public function testRendering()
    {
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

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "services" => [
                [
                    "id" => "1",
                    "name" => "Unittest Source Dienstleistung",
                    "maxQuantity" => 1,
                    "combinable" => null
                ]
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testServicesNotFound()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Request\\RequestNotFound';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('requestNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('requestNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
