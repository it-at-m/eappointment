<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesByServiceListTest extends Base
{

    protected $classname = "OfficesByServiceList";

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
            'serviceId' => '2'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }

    public function testRenderingRequestRelation()
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
            'serviceId' => '1'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleister',
                ],
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
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
            'serviceId' => '1,2'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleister',
                ],
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
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
            'serviceId' => '99999999'
        ], []);

        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'officesNotFound',
                    'errorMessage' => 'Office(s) not found for the provided serviceId(s).',                
                    'status' => 404,
                ]
            ],
            'status' => 404
        ];
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));

    }

    public function testNoServiceIdProvided()
    {
        $response = $this->render([], [], []);

        $expectedResponse = [
            'errors' => [
                [
                    'offices' => [],
                    'errorMessage' => 'Invalid serviceId(s).',
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
            'serviceId' => '2,99999999'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
                ]              
            ],
            'warning' => 'The following serviceId(s) were not found: 99999999'
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
    }

    public function testPartialResultsWithWarningRequestRelation()
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
            'serviceId' => '1,99999999'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleister',
                ],
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
                ]              
            ],
            'warning' => 'The following serviceId(s) were not found: 99999999'
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
    }

    public function testDuplicateServiceIds()
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
            'serviceId' => '2,2'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }

    public function testDuplicateServiceIdsCombinable()
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
            'serviceId' => '1,1'
        ], []);
        $expectedResponse = [
            'offices' => [
                [
                    'id' => '9999998',
                    'name' => 'Unittest Source Dienstleister',
                ],
                [
                    'id' => '9999999',
                    'name' => 'Unittest Source Dienstleister 2',
                ]
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
    }

    public function testInvalidServiceId()
    {
        $response = $this->render([], [
            'serviceId' => 'blahblahblah'
        ], []);
    
        $expectedResponse = [
            'errors' => [
                [
                    'offices' => [],
                    'errorMessage' => 'Invalid service ID: blahblahblah. Must be a number.',
                    'status' => 400,
                ]
            ]
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
    
}
