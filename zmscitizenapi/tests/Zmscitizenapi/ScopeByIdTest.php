<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Slim\Render;

class ScopeByIdTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\ScopeById";

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
            'id' => 1,
            'provider' => [
                '$schema' => 'https://schema.berlin.de/queuemanagement/provider.json',
                'id' => '9999998',
                'source' => 'unittest',
            ],
            'shortName' => 'Scope 1',
            'telephoneActivated' => true,
            'telephoneRequired' => false,
            'customTextfieldActivated' => true,
            'customTextfieldRequired' => false,
            'customTextfieldLabel' => 'Custom Label',
            'captchaActivatedRequired' => true,
            'displayInfo' => null,
        ];
        $responseBody = json_decode((string)$response->getBody(), true);
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
                    'errorCode' => 'invalidScopeId',
                    'errorMessage' => "scopeId should be a 32-bit integer.",
                    'status' => 400
                ]
            ],
            'status' => 400
        ];
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));

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
                    'errorCode' => 'invalidScopeId',
                    'errorMessage' => 'scopeId should be a 32-bit integer.',
                    'status' => 400
                ]
            ],
            "status" => 400
        ]; 
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
            
}
