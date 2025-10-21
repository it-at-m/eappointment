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
                    'url' => '/process/101002/fb43/ics/',
                    'parameters' => null,
                    'response' => $this->readFixture("GET_process_ics_template.json")
                ],
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
        error_log(json_encode($responseBody));
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
                'captchaToken' => '',
                'slotCount' => 1,
                'icsContent' => "BEGIN:VCALENDAR\r\nX-LOTUS-CHARSET:UTF-8\r\nCALSCALE:GREGORIAN\r\nVERSION:2.0\r\nPRODID:ZMS-München\r\nMETHOD:REQUEST\r\nX-WR-TIMEZONE:Europe/Berlin\r\nBEGIN:VTIMEZONE\r\nTZID:Europe/Berlin\r\nX-LIC-LOCATION:Europe/Berlin\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nTZOFFSETTO:+0200\r\nTZNAME:CEST\r\nDTSTART:19700329T020000\r\nRRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nTZOFFSETTO:+0100\r\nTZNAME:CET\r\nDTSTART:19701025T030000\r\nRRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\nBEGIN:VEVENT\r\nUID:20251029-102522\r\nORGANIZER;CN=\"Blah Landeshauptstadt München\":MAILTO:no-reply@muenchen.de\r\nSEQUENCE:0\r\nLOCATION:Landeshauptstadt München - Bürgerbüro Forstenrieder Allee, Fors\r\n tenrieder Allee 61a\r\nGEO:48.85299;2.36885\r\nSUMMARY:Reisepass\r\nDESCRIPTION:Guten Tag Tom Fink\\,\r\n Sie haben folgenden Termin bei uns gebucht: \r\n --- \r\n  **Terminnummer:**  102522 \r\n  **Leistung:**  \r\n 1x **[Reisepass ](https://stadt.muenchen.de/service/info/1063453/)**\r\n \r\n **Zeit:** \r\nMittwoch\\, 29.10.2025\\, 07:00 Uhr \r\n **Ort:** \r\nBürgerbüro Forstenrieder Allee\\, Forstenrieder Allee 61a\\, 81476\\, Münch\r\n en \r\nEingang 19B\\, Wartezone A – Erdgeschoss \r\n--- \r\n**Hinweise zur Vorbereitung:**\r\n- Tragen Sie den Termin in Ihren Kalender ein. Im Anhang finden Sie eine\r\n  ics-Datei zum Import.\r\n- Vergewissern Sie sich\\, dass Sie alle Voraussetzungen für die gebuchte\r\n n Leistungen erfüllen.\r\n- Tragen Sie alle benötigten Unterlagen zusammen und halten Sie sie für \r\n Ihren Termin bereit.\r\n--- \r\nIhnen ist etwas dazwischengekommen? Dann geben Sie uns Bescheid: \r\n[**Termin absagen oder verschieben**](https://service.berlin.de/terminvereinbarung/#/appointment/eyJpZCI6MTAwODIyLCJhdXRoS2V5IjoiOGNjZiJ9)\r\nMit freundlichen Grüßen\r\nLandeshauptstadt München \r\n![Logo der Landeshauptstadt München](https://assets.muenchen.de/logos/lh\r\n m/logo-lhm-muenchen-256.jpg)\r\n \r\nKreisverwaltungsreferat \r\nHauptabteilung II Bürgerangelegenheiten \r\nBürgerbüro Meldewesen\\, Kfz- und Fundangelegenheiten \r\nServicetelefon: [+49 89 233-96000](tel:+498923396000) \r\nNachricht: [Kontaktformular Bürgerbüro](https://service.muenchen.de/inte\r\n lliform/forms/01/02/02/buergerbuero_kontakt/index) \r\nWebsite: [muenchen.de/kvr](https://muenchen.de/kvr) \r\n---\r\n Dies ist eine automatisch erstellte Nachricht. Bitte antworten Sie nich\r\n t auf diese E-Mail.\r\nCLASS:PUBLIC\r\nDTSTART;TZID=Europe/Berlin:20251029T070000\r\nDTEND;TZID=Europe/Berlin:20251029T071500\r\nDTSTAMP:20251020T160550\r\nBEGIN:VALARM\r\nACTION:DISPLAY\r\nDESCRIPTION:München-Termin: 102522\r\nTRIGGER:-P1D\r\nEND:VALARM\r\nSTATUS:CONFIRMED\r\nEND:VEVENT\r\nEND:VCALENDAR",
            ]
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }

}
