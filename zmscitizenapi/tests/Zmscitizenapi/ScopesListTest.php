<?php

namespace BO\Zmscitizenapi\Tests;

class ScopesListTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\ScopesList";

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
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            "scopes" => [
                [
                    "id" => 1,
                    "provider" => [
                        "id" => 9999998,
                        "name" => null,
                        "source" => "unittest",
                        "contact" => null
                    ],
                    "shortName" => "Scope 1",
                    "telephoneActivated" => true,
                    "telephoneRequired" => false,
                    "customTextfieldActivated" => true,
                    "customTextfieldRequired" => false,
                    "customTextfieldLabel" => "Custom Label",
                    "captchaActivatedRequired" => true,
                    "displayInfo" => null
                ],
                [
                    "id" => 2,
                    "provider" => [
                        "id" => 9999999,
                        "name" => null,
                        "source" => "unittest",
                        "contact" => null
                    ],
                    "shortName" => "Scope 2",
                    "telephoneActivated" => false,
                    "telephoneRequired" => true,
                    "customTextfieldActivated" => false,
                    "customTextfieldRequired" => true,
                    "customTextfieldLabel" => "",
                    "captchaActivatedRequired" => false,
                    "displayInfo" => null
                ]
            ]
        ];
        

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
