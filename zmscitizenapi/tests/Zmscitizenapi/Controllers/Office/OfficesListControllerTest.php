<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Office;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;
class OfficesListControllerTest extends ControllerTestCase
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Office\OfficesListController";

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
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "offices" => [
                [
                    "id" => 9999998,
                    "name" => "Unittest Source Dienstleister",
                    "address" => null,
                    "showAlternativeLocations" => false,
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
                    "disabledByServices" => [],
                    "priority" => 1,
                    "scope" => [
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
                        "emailFrom" => "no-reply@muenchen.de",
                        'emailRequired' => false,
                        "telephoneActivated" => true,
                        "telephoneRequired" => false,
                        "customTextfieldActivated" => true,
                        "customTextfieldRequired" => false,
                        "customTextfieldLabel" => "Custom Label",
                        "customTextfield2Activated" => true,
                        "customTextfield2Required" => false,
                        "customTextfield2Label" => "Second Custom Label",
                        "captchaActivatedRequired" => false,
                        "displayInfo" => null,
                        "slotsPerAppointment" => null
                    ],
                    "maxSlotsPerAppointment" => null
                ],
                [
                    "id" => 9999999,
                    "name" => "Unittest Source Dienstleister 2",
                    "address" => null,
                    "showAlternativeLocations" => true,
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
                    "disabledByServices" => [],
                    "priority" => 1,
                    "scope" => [
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
                        "emailFrom" => "no-reply@muenchen.de",
                        'emailRequired' => true,
                        "telephoneActivated" => false,
                        "telephoneRequired" => true,
                        "customTextfieldActivated" => false,
                        "customTextfieldRequired" => true,
                        "customTextfieldLabel" => "",
                        "customTextfield2Activated" => false,
                        "customTextfield2Required" => true,
                        "customTextfield2Label" => "",
                        "captchaActivatedRequired" => false,
                        "displayInfo" => null,
                        "slotsPerAppointment" => null
                    ],
                    "maxSlotsPerAppointment" => null
                ]
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testOfficesNotFound()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Provider\\ProviderNotFound';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('providerNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('providerNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    public function testInternalError()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Source\\SourceNotFound';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'exception' => $exception
            ]
        ]);

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            'errors' => [
                ErrorMessages::get('sourceNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('sourceNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
