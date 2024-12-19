<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesListTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\OfficesList";

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
            "offices" => [
                [
                    "id" => 9999998,
                    "name" => "Unittest Source Dienstleister",
                    "address" => null,
                    "geo" => null,
                    "scope" => [
                        "id" => 1,
                        "provider" => [
                            '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                            "id" => "9999998",
                            "source" => "unittest"
                        ],
                        "shortName" => "Scope 1",
                        "telephoneActivated" => true,
                        "telephoneRequired" => false,
                        "customTextfieldActivated" => true,
                        "customTextfieldRequired" => false,
                        "customTextfieldLabel" => "Custom Label",
                        "captchaActivatedRequired" => true,
                        "displayInfo" => null
                    ]
                ],
                [
                    "id" => 9999999,
                    "name" => "Unittest Source Dienstleister 2",
                    "address" => null,
                    "geo" => null,
                    "scope" => [
                        "id" => 2,
                        "provider" => [
                            '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                            "id" => "9999999",
                            "source" => "unittest"
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
            ]
        ]; 
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
