<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Localization\ErrorMessages;

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
                'id' => '9999998',
                "name" => null,
                "source" => "unittest",
                "contact" => null
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
                ErrorMessages::get('scopesNotFound')
            ]
        ];

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
        
    }
    
    public function testNoScopeIdProvided()
    {
        $response = $this->render([], [], []);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('invalidScopeId')
            ]
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
                ErrorMessages::get('invalidScopeId')
            ]
        ]; 
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, json_decode((string)$response->getBody(), true));
    }
            
}
