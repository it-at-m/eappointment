<?php

namespace BO\Zmscitizenbackend\Tests\Controllers\Office;

use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Models\Collections\ServiceList;
use BO\Zmscitizenbackend\Repository\Office\OfficesServicesRelationsHydrator;
use BO\Zmscitizenbackend\Repository\Office\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Helper\OfficesServicesRelationsRows;

class OfficesServicesRelationsControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Controllers\Office\OfficesServicesRelationsController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }

        $this->stubRepository();
    }

    public function tearDown(): void
    {
        OfficesServicesRelationsRepository::use(null);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        \App::$ACCESS_UNPUBLISHED_ON_DOMAIN = '';
        parent::tearDown();
    }

    public function testRendering()
    {
        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($this->expectedPublicResponse(), $responseBody);
    }

    public function testDisplayNotPublicRequests()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'localhost';
        \App::$ACCESS_UNPUBLISHED_ON_DOMAIN = 'localhost';

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($this->expectedUnpublishedResponse(), $responseBody);
    }

    public function testEmptyList()
    {
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readOfficesAndServices')->willReturn(
            new OfficeServiceAndRelationList(new OfficeList(), new ServiceList(), new OfficeServiceRelationList())
        );
        OfficesServicesRelationsRepository::use($repository);

        $response = $this->render();
        $responseBody = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(
            ['offices' => [], 'services' => [], 'relations' => []],
            $responseBody
        );
    }

    private function stubRepository(): void
    {
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readOfficesAndServices')->willReturnCallback(
            static function (bool $showUnpublished = false): OfficeServiceAndRelationList {
                return (new OfficesServicesRelationsHydrator())->hydrate(
                    OfficesServicesRelationsRows::officeRows(),
                    OfficesServicesRelationsRows::requestRows(),
                    OfficesServicesRelationsRows::relationRows(),
                    $showUnpublished
                );
            }
        );
        OfficesServicesRelationsRepository::use($repository);
    }

    private function expectedPublicResponse(): array
    {
        $expected = $this->expectedUnpublishedResponse();
        $expected['services'] = [$expected['services'][0]];
        return $expected;
    }

    private function expectedUnpublishedResponse(): array
    {
        return [
            "offices" => [
                [
                    "id" => 9999998,
                    "name" => "Unittest",
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
                        "reservationDuration" => null,
                        "activationDuration" => null,
                        "hint" => null
                    ],
                    "slotsPerAppointment" => null,
                    "parentId" => null,
                    "allowDisabledServicesMix" => null,
                    "sharedBookingOfficeIds" => null,
                ],
                [
                    "id" => 9999999,
                    "name" => "Unittest 2",
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
                        "reservationDuration" => null,
                        "activationDuration" => null,
                        "hint" => null
                    ],
                    "slotsPerAppointment" => null,
                    "parentId" => null,
                    "allowDisabledServicesMix" => null,
                    "sharedBookingOfficeIds" => null,
                ]
            ],
            "services" => [
                [
                    "id" => 1,
                    "name" => "Unittest Source Dienstleistung",
                    "maxQuantity" => 1,
                    "combinable" => [],
                    "parentId" => null,
                    "variantId" => null,
                    "rootParentId" => 1,
                    "showOnStartPage" => true
                ],
                [
                    "id" => 2,
                    "name" => "Unittest Source Dienstleistung 2",
                    "maxQuantity" => 1,
                    "combinable" => [
                        "1" => ["1" => [9999999]],
                        "2" => ["2" => [9999999]]
                    ],
                    "parentId" => null,
                    "variantId" => null,
                    "rootParentId" => 2,
                    "showOnStartPage" => true
                ]
            ],
            "relations" => [
                [
                    "officeId" => 9999998,
                    "serviceId" => 1,
                    "slots" => 2,
                    "public" => true,
                    "maxQuantity" => null
                ],
                [
                    "officeId" => 9999999,
                    "serviceId" => 1,
                    "slots" => 1,
                    "public" => true,
                    "maxQuantity" => null
                ],
                [
                    "officeId" => 9999999,
                    "serviceId" => 2,
                    "slots" => 1,
                    "public" => true,
                    "maxQuantity" => null
                ]
            ]
        ];
    }
}
