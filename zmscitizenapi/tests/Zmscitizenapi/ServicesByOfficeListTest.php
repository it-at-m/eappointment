<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesByOfficeListTest extends Base
{

    protected $classname = "ServicesByOfficeList";

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
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
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
                    'id' => '1',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ],
                [
                    'id' => '2',
                    'name' => 'Unittest Source Dienstleistung 2',
                    'maxQuantity' => 1,
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
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
            'errors' => [
                [
                    'errorCode' => 'servicesNotFound',
                    'errorMessage' => 'Service(s) not found for the provided officeId(s).',
                    'status' => 404
                ]
            ],
            'status' => 404
        ];

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
   
    }

    public function testNoOfficeIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'errors' => [
                [
                    'services' => [],
                    'errorMessage' => 'Invalid officeId(s)',
                    'status' => 400
                ]
            ],
            'status' => 400
        ];
        
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
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
                    'id' => '1',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ]
            ],
            'warning' => 'The following officeId(s) were not found: 99999999'
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
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
                    'id' => '1',
                    'name' => 'Unittest Source Dienstleistung',
                    'maxQuantity' => 1,
                ]
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
}
