<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesByOfficeListTest extends Base
{
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
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }

    public function testRenderingMulti()
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
            'officeId' => '9999998,9999999'
        ], []);
        $expectedResponse = [
            'services' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ],
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleistung 2',
                    'maxQuantity' => 1,
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
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
            'services' => [],
            'error' => 'Service(s) not found for the provided officeId(s)'
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testNoOfficeIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'services' => [],
            'error' => 'Invalid officeId(s)'
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testPartialResultsWithWarning()
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
            'officeId' => '9999998,99999999'
        ], []);
        $expectedResponse = [
            'services' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ]
            ],
            'warning' => 'The following officeId(s) were not found: 99999999'
        ];    
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDuplicateOfficeIds()
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
            'officeId' => '9999998,9999998'
        ], []);
        $expectedResponse = [
            'services' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
}
