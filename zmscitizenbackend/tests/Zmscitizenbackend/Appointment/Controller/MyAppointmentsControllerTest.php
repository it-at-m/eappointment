<?php

namespace BO\Zmscitizenbackend\Tests\Appointment\Controller;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Appointment\Repository\MyAppointmentsRepository;
use BO\Zmscitizenbackend\Tests\ControllerTestCase;
use BO\Zmscitizenbackend\Tests\Appointment\Helper\AppointmentByIdRows;
use PHPUnit\Framework\Attributes\DataProvider;

class MyAppointmentsControllerTest extends ControllerTestCase
{
    protected $classname = "\BO\Zmscitizenbackend\Appointment\Controller\MyAppointmentsController";

    public function setUp(): void
    {
        parent::setUp();

        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    public function tearDown(): void
    {
        MyAppointmentsRepository::use(null);
        parent::tearDown();
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

    public function testRendering()
    {
        $this->stubAppointments([$this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")]);
        $token_part = base64_encode(json_encode(['lhmExtID' => 'ext_1']));
        $response = $this->render([], [
            '__header' => [
                'Authorization' => 'Bearer .' . $token_part . '.',
            ],
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return $response;
    }

    #[DataProvider('unauthenticatedHeaderProvider')]
    public function testUnauthenticated(array $headers)
    {
        $parameters = [
            '__header' => $headers
        ];
        $response = $this->render([], $parameters);
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

    public static function basicRenderingProvider(): array
    {
        return [
            [
                null,
                false,
            ],
            [
                101002,
                false,
            ],
            [
                null,
                true,
            ],
        ];
    }

    #[DataProvider('basicRenderingProvider')]
    public function testBasicRendering(?int $filterId, bool $emptyOptionalOidcClaims)
    {
        $captured = [];
        $this->stubAppointments(
            [$this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")],
            $captured
        );

        $additionalParameters = [];
        if (!empty($filterId)) {
            $additionalParameters['filterId'] = $filterId;
        }

        $claims = [
            'lhmExtID' => 'ext_1',
            'email' => 'test@example.com',
            'given_name' => 'Test',
            'family_name' => 'User',
        ];
        if ($emptyOptionalOidcClaims) {
            unset($claims['email']);
            unset($claims['given_name']);
            unset($claims['family_name']);
        }
        $token_part = base64_encode(json_encode($claims));
        $parameters = [
            '__header' => [
                'Authorization' => 'Bearer .' . $token_part . '.',
            ],
            ...$additionalParameters,
        ];
        $response = $this->render([], $parameters);
        $responseBody = json_decode((string) $response->getBody(), true);

        $expectedResponse = json_decode(
            json_encode([$this->sampleAppointment("BEGIN:VCALENDAR\r\nEND:VCALENDAR")->toArray()]),
            true
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
        $this->assertSame('ext_1', $captured['externalUserId'] ?? null);
        $this->assertSame($filterId, $captured['filterId'] ?? null);
        $this->assertSame('confirmed', $captured['status'] ?? null);
    }

    public function testEmptyList()
    {
        $this->stubAppointments([]);

        $claims = [
            'lhmExtID' => 'ext_1',
        ];
        $token_part = base64_encode(json_encode($claims));
        $parameters = [
            '__header' => [
                'Authorization' => 'Bearer .' . $token_part . '.',
            ],
        ];
        $response = $this->render([], $parameters);
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame([], $responseBody);
    }

    /**
     * @param list<ThinnedProcess> $appointments
     * @param array<string, mixed> $captured
     */
    private function stubAppointments(array $appointments, array &$captured = []): void
    {
        $repository = $this->createStub(MyAppointmentsRepository::class);
        $repository->method('readAppointmentsForUser')->willReturnCallback(
            static function (
                string $externalUserId,
                ?int $filterId = null,
                ?string $status = null
            ) use (
                $appointments,
                &$captured
            ): array {
                $captured['externalUserId'] = $externalUserId;
                $captured['filterId'] = $filterId;
                $captured['status'] = $status;
                return $appointments;
            }
        );
        MyAppointmentsRepository::use($repository);
    }

    private function sampleAppointment(?string $icsContent = null): ThinnedProcess
    {
        $appointment = (new AppointmentByIdHydrator())->hydrate(
            AppointmentByIdRows::processRow(),
            AppointmentByIdRows::requestRows(),
            $icsContent
        );
        $appointment->setCaptchaToken('');
        return $appointment;
    }
}
