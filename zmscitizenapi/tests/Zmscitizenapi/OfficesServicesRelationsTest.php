<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesServicesRelationsTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\OfficesServicesRelations";

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
                            "id" => "9999998",
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
                            "id" => "9999999",
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
            ],
            "services" => [
                [
                    "id" => 1,
                    "name" => "Unittest Source Dienstleistung",
                    "maxQuantity" => 1,
                    "combinable" => []
                ],
                [
                    "id" => 2,
                    "name" => "Unittest Source Dienstleistung 2",
                    "maxQuantity" => 1,
                    "combinable" => [
                        1 => [9999999],
                        2 => [9999999]
                    ]
                ]
            ],
            "relations" => [
                [
                    "officeId" => 9999998,
                    "serviceId" => 1,
                    "slots" => 2
                ],
                [
                    "officeId" => 9999999,
                    "serviceId" => 1,
                    "slots" => 1
                ],
                [
                    "officeId" => 9999999,
                    "serviceId" => 2,
                    "slots" => 1
                ]
            ]
        ]; 
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
    
}
