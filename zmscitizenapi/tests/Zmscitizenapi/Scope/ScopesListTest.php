<?php

namespace BO\Zmscitizenapi\Tests\Scope;

use BO\Zmscitizenapi\Tests\Base;

class ScopesListTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Scope\ScopesListController";

    public function setUp(): void
    {
        parent::setUp();
        
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

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
                        "name" => "Unittest Source Dienstleister",
                        "lat" => 48.12750898398659,
                        "lon" => 11.604317899956524,
                        "source" => "unittest",
                        "contact" => [
                            "city" => "Berlin",
                            "country" => "Germany",
                            "name" => "Unittest Source Dienstleister",
                            "postalCode" => "10178",
                            "region" => "Berlin",
                            "street" => "Alte Jakobstraße",
                            "streetNumber" => "105"
                        ]
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
                        "name" => "Unittest Source Dienstleister 2",
                        "lat" => 48.12750898398659,
                        "lon" => 11.604317899956524,
                        "source" => "unittest",
                        "contact" => [
                            "city" => "Berlin",
                            "country" => "Germany",
                            "name" => "Unittest Source Dienstleister 2",
                            "postalCode" => "10178",
                            "region" => "Berlin",
                            "street" => "Alte Jakobstraße",
                            "streetNumber" => "106"
                        ]
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
