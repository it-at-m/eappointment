<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Slim\Render;

class ScopeByIdGetTest extends Base
{

    protected $classname = "ScopeByIdGet";

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
            'scopeId' => '1'
        ], []);
        $expectedResponse = [
            [
                'id' => '1',
                'provider' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => '9999998',
                    'source' => 'unittest',
                ],
                'shortName' => 'Scope 1',
                'telephoneActivated' => '1',
                'telephoneRequired' => '0',
                'customTextfieldActivated' => '1',
                'customTextfieldRequired' => '0',
                'customTextfieldLabel' => 'Custom Label',
                'captchaActivatedRequired' => '1',
                'displayInfo' => null
            ]
        ];
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
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
            'scopeId' => '1,2'
        ], []);
        $expectedResponse = [
            [
                'id' => '1',
                'provider' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => '9999998',
                    'source' => 'unittest',
                ],
                'shortName' => 'Scope 1',
                'telephoneActivated' => '1',
                'telephoneRequired' => '0',
                'customTextfieldActivated' => '1',
                'customTextfieldRequired' => '0',
                'customTextfieldLabel' => 'Custom Label',
                'captchaActivatedRequired' => '1',
                'displayInfo' => null
            ],
            [
                'id' => '2',
                'provider' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => '9999999',
                    'source' => 'unittest',
                ],
                'shortName' => 'Scope 2',
                'telephoneActivated' => '0',
                'telephoneRequired' => '1',
                'customTextfieldActivated' => '0',
                'customTextfieldRequired' => '1',
                'customTextfieldLabel' => '',
                'captchaActivatedRequired' => '0',
                'displayInfo' => null
            ]
        ];
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testScopeNotFound()
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
            'scopeId' => '99'
        ], []);

        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => "scopesNotFound",
                    'errorMessage' => 'Scope(s) not found.',
                    'status' => 404
                ]
            ],
            'status' => 404
        ];

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
    }
    
    public function testNoScopeIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'errors' => [
                [
                    'services' => [],
                    'errorMessage' => 'Invalid scopeId(s).',
                    'status' => 400
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));

    }
    
    public function testPartialResultsWithWarning() //Don't return invalid scopes thta don't exist like 99
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
            'scopeId' => '1,99'
        ], []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            [
                'id' => '1',
                'provider' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => '9999998',
                    'source' => 'unittest',
                ],
                'shortName' => 'Scope 1',
                'telephoneActivated' => '1',
                'telephoneRequired' => '0',
                'customTextfieldActivated' => '1',
                'customTextfieldRequired' => '0',
                'customTextfieldLabel' => 'Custom Label',
                'captchaActivatedRequired' => '1',
                'displayInfo' => null
            ]
        ];
    
        $this->assertEquals(200, $response->getStatusCode());  
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
    
    public function testDuplicateScopeIds()
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
            'scopeId' => '1,1,1'
        ], []);
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            [
                'id' => '1',
                'provider' => [
                    '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                    'id' => '9999998',
                    'source' => 'unittest',
                ],
                'shortName' => 'Scope 1',
                'telephoneActivated' => '1',
                'telephoneRequired' => '0',
                'customTextfieldActivated' => '1',
                'customTextfieldRequired' => '0',
                'customTextfieldLabel' => 'Custom Label',
                'captchaActivatedRequired' => '1',
                'displayInfo' => null
            ]
        ];
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    

    public function testInvalidScopeId()
    {
        $response = $this->render([], [
            'scopeId' => 'blahblahblah'
        ], []);
    
        $expectedResponse = [
            'errors' => [
                [
                    'scopes' => [],
                    'errorMessage' => 'Invalid scope ID: blahblahblah. Must be a number.',
                    'status' => 400
                ]
            ]
        ]; 
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
            
}
