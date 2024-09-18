<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesByServiceListTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\OfficesByServiceList";

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
            'status' => 404,
            'offices' => [],
            'error' => 'Office(s) not found for the provided serviceId(s)'
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testNoServiceIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'status' => 400,
            'offices' => [],
            'error' => 'Invalid serviceId(s)'
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
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(200, $response->getStatusCode());
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
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        $this->assertEquals(200, $response->getStatusCode());
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
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
}
