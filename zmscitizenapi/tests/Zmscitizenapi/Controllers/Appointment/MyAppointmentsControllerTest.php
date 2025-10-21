<?php

namespace BO\Zmscitizenapi\Tests\Controllers\Appointment;

use BO\Zmscitizenapi\Tests\ControllerTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MyAppointmentsControllerTest extends ControllerTestCase
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\Appointment\MyAppointmentsController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    public static function unauthenticatedHeaderProvider(): array
    {
        return [
            [[]],
            [
                [
                    'Authorization' => ''
                ],
            ],
            [
                [
                    'Authorization' => 'Bearer '
                ],
            ],
            [
                [
                    'Authorization' => 'Bearer xxx'
                ],
            ],
            [
                [
                    'Authorization' => 'Bearer xxx.xxx.xxx'
                ],
            ]
        ];
    }

    #[DataProvider('unauthenticatedHeaderProvider')]
    public function testUnauthenticated(array $headers)
    {
        $parameters = [
            '__header' => $headers
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            'errors' => [
                [
                    'errorCode' => 'authKeyMismatch',
                    'errorMessage' => 'Invalid authentication key.',
                    'statusCode' => 406,
                    'errorType' => 'warning',
                ]
            ]
        ];

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

    // overriding base method
    public function testRendering() {
        $this->assertTrue(true);
    }

    public static function filterParameterProvider(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    "filterId" => 101002,
                ]
            ]
        ];
    }

    #[DataProvider('filterParameterProvider')]
    public function testBasicRendering(array $providedParameters)
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/externaluserid/ext_1/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'status' => 'confirmed',
                        ...$providedParameters
                    ],
                    'response' => $this->readFixture("GET_process.json")
                ],
            ]
        );

        $token_part = base64_encode(
            json_encode([
                'lhmExtID' => 'ext_1',
                'email' => 'test@example.com',
                'given_name' => 'Test',
                'family_name' => 'User',
            ])
        );
        $parameters = [
            '__header' => [
                'Authorization' => 'Bearer .'.$token_part.'.',
            ],
            ...$providedParameters,
        ];
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = [
            [
                'processId' => 101002,
                'timestamp' => '1724907600',
                'authKey' => 'fb43',
                'familyName' => 'Doe',
                'customTextfield' => '',
                'customTextfield2' => '',
                'email' => 'johndoe@example.com',
                'telephone' => '0123456789',
                'officeName' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                'officeId' => 102522,
                'scope' => [
                    'id' => 64,
                    'provider' => [
                        'id' => 102522,
                        'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
                        'displayName' => 'Bürgerbüro Orleansplatz DEV',
                        'lat' => null,
                        'lon' => null,
                        'source' => 'dldb',
                        'contact' => [
                            "city" => "Muenchen",
                            "country" => "Germany",
                            "name" => "Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)",
                            "postalCode" => "81667",
                            "region" => "Muenchen",
                            "street" => "Orleansstraße",
                            "streetNumber" => "50"
                        ],
                    ],
                    'shortName' => 'DEVV',
                    'emailFrom' => 'no-reply@muenchen.de',
                    'emailRequired' => null,
                    'telephoneActivated' => null,
                    'telephoneRequired' => null,
                    'customTextfieldActivated' => null,
                    'customTextfieldRequired' => null,
                    'customTextfieldLabel' => null,
                    'customTextfield2Activated' => null,
                    'customTextfield2Required' => null,
                    'customTextfield2Label' => null,
                    'captchaActivatedRequired' => null,
                    'infoForAppointment' => null,
                    'infoForAllAppointments' => null,
                    'slotsPerAppointment' => null,
                    "appointmentsPerMail" => null,
                    "whitelistedMails" => null,
                    "reservationDuration" => 15,
                    "activationDuration" => 15,
                    "hint" => null
                ],
                'subRequestCounts' => [],
                'serviceId' => 1063424,
                'serviceName' => 'Gewerbe anmelden',
                'serviceCount' => 1,
                'status' => 'confirmed',
                'slotCount' => 1,
                'captchaToken' => '',
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
