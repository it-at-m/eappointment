<?php

namespace BO\Zmscitizenapi\Tests;

class ScopesListTest extends Base
{

    protected $classname = "ScopesList";

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
                    "id" => "1",
                    "provider" => [
                        '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                        "id" => "9999998",
                        "source" => "unittest"
                    ],
                    "shortName" => "Scope 1",
                    "telephoneActivated" => "1",
                    "telephoneRequired" => "0",
                    "customTextfieldActivated" => "1",
                    "customTextfieldRequired" => "0",
                    "customTextfieldLabel" => "Custom Label",
                    "captchaActivatedRequired" => "1",
                    "displayInfo" => null
                ],
                [
                    "id" => "2",
                    "provider" => [
                        '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                        "id" => "9999999",
                        "source" => "unittest"
                    ],
                    "shortName" => "Scope 2",
                    "telephoneActivated" => "0",
                    "telephoneRequired" => "1",
                    "customTextfieldActivated" => "0",
                    "customTextfieldRequired" => "1",
                    "customTextfieldLabel" => "",
                    "captchaActivatedRequired" => "0",
                    "displayInfo" => null
                ]
            ],
            "status" => 200,
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
