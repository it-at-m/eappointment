<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesListTest extends Base
{

    protected $classname = "OfficesList";

    public function testRendering() {
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
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([
            "offices" => [
                [
                    "id" => "9999998",
                    "name" => "Unittest Source Dienstleister",
                    "scope" => [
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
                    ]
                ],
                [
                    "id" => "9999999",
                    "name" => "Unittest Source Dienstleister 2",
                    "scope" => [
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
                ]
            ]
        ], $responseData);
    }
}
