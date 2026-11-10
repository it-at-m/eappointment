<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Mail\Service\MailTemplates;
use BO\Zmsbackend\Process\Service\Process as ProcessService;
use BO\Zmsbackend\Scope\Service\Scope as ScopeService;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Mail;
use PHPUnit\Framework\Attributes\DataProvider;

class ProcessDeleteMailTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessDeleteMail";

    const int PROCESS_ID = 10029;

    const string AUTHKEY = '1c56';

    #[\Override]
    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => $this->readFixture('GetProcess_10029.json')
        ], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        return $response;
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "1234",
                "scope": {
                    "id": 141,
                    "provider": {
                        "id": 123456,
                        "name": "Flughafen Schönefeld, Aufsicht",
                        "source": "dldb"
                    },
                    "shortName": "Zentrale"
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141,
                            "provider": {
                                "id": 123456,
                                "name": "Flughafen Schönefeld, Aufsicht",
                                "source": "dldb"
                            },
                            "shortName": "Zentrale"
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141,
                    "provider": {
                        "id": 123456,
                        "name": "Flughafen Schönefeld, Aufsicht",
                        "source": "dldb"
                    },
                    "shortName": "Zentrale"
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "email": "max@service.berlin.de",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141,
                            "provider": {
                                "id": 123456,
                                "name": "Flughafen Schönefeld, Aufsicht",
                                "source": "dldb"
                            },
                            "shortName": "Zentrale"
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
    }

    public function testMissingMailOnPayloadUsesStoredClient()
    {
        $response = $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "scope": {
                    "id": 141,
                    "preferences": {
                        "client": {
            				"emailRequired": 1
            			}
                    }
                },
                "clients": [
                    {
                        "familyName": "Max Mustermann",
                        "telephone": "030 115"
                    }
                ],
                "appointments" : [
                    {
                        "date": 1447869172,
                        "scope": {
                            "id": 141
                        },
                        "slotCount": 2
                    }
                ],
                "status": "confirmed"
            }'
        ], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUnvalidInput()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "status": "unvalid"
            }'
        ], []);
    }

    #[DataProvider('originalDataProvider')]
    public function testOriginalDataInCancellationMail(
        bool $hasOriginalDate,
        bool $hasOriginalRequests,
        bool $isAdmin
    ): void {
        $processService = new ProcessService();
        $storedProcess = $processService->readEntity(
            self::PROCESS_ID,
            self::AUTHKEY
        );

        $storedDate = (int) $storedProcess->getFirstAppointment()->date;
        $storedRequests = json_encode(
            $storedProcess->getRequests(),
            JSON_THROW_ON_ERROR
        );
        $originalDate = $storedDate - 86400;

        $payload = json_decode(
            $this->readFixture('GetProcess_10029.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($hasOriginalDate) {
            $payload['appointments'][0]['date'] = $originalDate;
        } else {
            unset($payload['appointments']);
        }

        if ($hasOriginalRequests) {
            $originalRequest = [
                'id' => '120703',
                'source' => 'dldb',
                'name' => 'Original service for regression test',
            ];

            $payload['requests'] = [
                $originalRequest,
                $originalRequest,
            ];
        } else {
            unset($payload['requests']);
        }

        $payload['clients'][0]['email'] = 'ignored@example.invalid';

        if ($isAdmin) {
            $this->setWorkstation()
                ->getUseraccount()
                ->setPermissions('appointment');
        }

        $mail = $this->renderCancellationMail(
            $payload,
            $isAdmin ? 'admin' : null
        );

        $html = $mail->getHtmlPart();
        $this->assertIsString($html);

        $expectedDate = $isAdmin && $hasOriginalDate
            ? $originalDate
            : $storedDate;

        $this->assertStringContainsString(
            'DATE=' . $expectedDate . ';',
            $html
        );

        if ($isAdmin && $hasOriginalRequests) {
            $this->assertStringContainsString(
                'SERVICE=Original service for regression test;COUNT=2;',
                $html
            );
        } else {
            $this->assertStringNotContainsString(
                'Original service for regression test',
                $html
            );

            foreach ($storedProcess->getRequests() as $storedRequest) {
                $this->assertStringContainsString(
                    'SERVICE=' . $storedRequest->name . ';',
                    $html
                );
            }
        }

        $this->assertSame(
            $storedProcess->getFirstClient()->email,
            $mail->getClient()->email
        );

        $processAfterMail = $processService->readEntity(
            self::PROCESS_ID,
            self::AUTHKEY
        );

        $this->assertSame(
            $storedDate,
            (int) $processAfterMail->getFirstAppointment()->date
        );
        $this->assertSame(
            $storedRequests,
            json_encode(
                $processAfterMail->getRequests(),
                JSON_THROW_ON_ERROR
            )
        );
    }

    public static function originalDataProvider(): array
    {
        return [
            'original date and services' => [true, true, true],
            'original services without appointment' => [false, true, true],
            'original date without services' => [true, false, true],
            'no original data' => [false, false, true],
            'non-admin ignores original data' => [true, true, false],
        ];
    }

    #[DataProvider('permissionCheckProvider')]
    public function testOriginalDataRequiresAppointmentPermission(
        bool $includeAppointment
    ): void {
        $this->setWorkstation();

        $payload = json_decode(
            $this->readFixture('GetProcess_10029.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($includeAppointment) {
            unset($payload['requests']);
        } else {
            unset($payload['appointments']);
        }

        $this->expectException(UserAccountMissingRights::class);
        $this->expectExceptionCode(403);

        $this->renderCancellationMail($payload, 'admin');
    }

    public static function permissionCheckProvider(): array
    {
        return [
            'date copy requires permission' => [true],
            'service copy requires permission' => [false],
        ];
    }

    private function renderCancellationMail(
        array $payload,
        ?string $initiator
    ): Mail {
        $template = 'DATE={{ date }};'
            . 'SCOPE_ID={{ process.scope.id }};'
            . 'SCOPE={{ process.scope.contact.name }};'
            . '{% for group in requestGroups %}'
            . 'SERVICE={{ group.request.name }};COUNT={{ group.count }};'
            . '{% endfor %}';

        $templates = new MailTemplates();
        $templates->updateTemplateContent('mail_admin_delete.twig', $template);
        $templates->updateTemplateContent('mail_delete.twig', $template);

        $parameters = [
            '__body' => json_encode($payload, JSON_THROW_ON_ERROR),
        ];

        if ($initiator !== null) {
            $parameters['initiator'] = $initiator;
        }

        $response = $this->render([], $parameters, []);

        $this->assertSame(200, $response->getStatusCode());

        $result = json_decode(
            (string) $response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return new Mail($result['data']);
    }

    public function testAdminUsesOriginalScopeWithoutDateOrServices(): void
    {
        $originalScopeId = 141;
        $originalScope = (new ScopeService())->readEntity($originalScopeId, 1);
        $this->assertNotNull($originalScope);

        $processService = new ProcessService();
        $storedProcess = $processService->readEntity(self::PROCESS_ID, self::AUTHKEY);
        $storedScopeId = (int) $storedProcess->getScopeId();
        $this->assertNotSame($originalScopeId, $storedScopeId);

        $workstation = $this->setWorkstation(scopeId: $originalScopeId);
        $workstation['queue'] = ['clusterEnabled' => 0];
        $workstation->getUseraccount()->setPermissions('appointment');

        $payload = json_decode(
            $this->readFixture('GetProcess_10029.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        unset($payload['appointments'], $payload['requests']);
        $payload['scope']['id'] = $originalScopeId;
        $payload['scope']['contact']['name'] = 'Scope name from request';

        $mail = $this->renderCancellationMail($payload, 'admin');
        $html = $mail->getHtmlPart();
        $this->assertIsString($html);

        $this->assertStringContainsString('SCOPE_ID=141;', $html);
        $this->assertStringContainsString(
            'SCOPE=' . $originalScope->contact->name . ';',
            $html
        );
        $this->assertStringNotContainsString('Scope name from request', $html);

        $processAfterMail = $processService->readEntity(self::PROCESS_ID, self::AUTHKEY);
        $this->assertSame($storedScopeId, (int) $processAfterMail->getScopeId());
    }
}
