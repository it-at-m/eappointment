<?php

namespace BO\Zmscitizenapi\Tests\Service;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\Base;

class ServiceListByOfficeTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Service\ServiceListByOfficeController";

    public function setUp(): void
    {
        parent::setUp();
        
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
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
                'response' => $this->readFixture("GET_SourceGet_dldb.json"),
            ]
        ]);
        $response = $this->render([], [
            'officeId' => '9999998'
        ], []);
        $expectedResponse = [
            'services' => [
                [
                    'id' => '1',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                    "combinable" => null
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string) $response->getBody(), true));
    }


    public function testServiceNotFound()
    {
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json"),
            ]
        ]);

        $response = $this->render([], [
            'officeId' => '99999999'
        ], []);

        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('servicesNotFound')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('servicesNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string) $response->getBody(), true));

    }

    public function testNoOfficeIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidOfficeId')
            ]
        ];

        $this->assertEquals(ErrorMessages::get('invalidOfficeId')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string) $response->getBody(), true));

    }
    
}
