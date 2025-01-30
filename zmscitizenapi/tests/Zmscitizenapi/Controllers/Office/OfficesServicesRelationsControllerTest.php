<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Office;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Tests\ControllerTestCase;

class OfficesServicesRelationsControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\Office\OfficesServicesRelationsController";

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
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
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
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
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

    public function testDisplayNotPublicRequests()
    {
        \App::$ACCESS_UNPUBLISHED_ON_DOMAIN = 'localhost';
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
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
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
                    "displayNameAlternatives" => [],
                    "organization" => null,
                    "organizationUnit" => null,
                    "slotTimeInMinutes" => null,
                    "geo" => [
                        "lat" => "48.12750898398659",
                        "lon" => "11.604317899956524"
                    ],
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
                        "1" => [9999999],
                        "2" => [9999999]
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

    public function testServicesNotFound()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\\Zmsapi\\Exception\\Request\\RequestNotFound';

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
                ErrorMessages::get('requestNotFound')
            ]
        ];
        $this->assertEquals(ErrorMessages::get('requestNotFound')['statusCode'], $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
