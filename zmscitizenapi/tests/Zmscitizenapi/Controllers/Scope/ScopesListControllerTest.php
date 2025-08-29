<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Scope;

use BO\Zmscitizenapi\Tests\ControllerTestCase;

class ScopesListControllerTest extends ControllerTestCase
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
                        "displayName" => "Unittest",
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
                    "infoForAppointment" => null,
                    "infoForAllAppointments" => null,
                    "slotsPerAppointment" => null,
                    "appointmentsPerMail" => null,
                    "whitelistedMails" => null,
                    "reservationDuration" => null
                ],
                [
                    "id" => 2,
                    "provider" => [
                        "id" => 9999999,
                        "name" => "Unittest Source Dienstleister 2",
                        "displayName" => "Unittest 2",
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
                    "infoForAppointment" => null,
                    "infoForAllAppointments" => null,
                    "slotsPerAppointment" => null,
                    "appointmentsPerMail" => null,
                    "whitelistedMails" => null,
                    "reservationDuration" => null
                    ]
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
