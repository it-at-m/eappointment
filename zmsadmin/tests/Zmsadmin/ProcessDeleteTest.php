<?php

namespace BO\Zmsadmin\Tests;

class ProcessDeleteTest extends Base
{
    protected $arguments = [
        'id' => '82252'
    ];

    protected $parameters = [
        'initiator' => 'admin'
    ];

    protected $classname = "ProcessDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/process/82252/',
                    'parameters' => ['initiator' => 'admin'],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/delete/mail/',
                    'response' => $this->readFixture("POST_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/delete/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            '82252',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteWithoutAppointmentAndNotificationDisabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100632/',
                    'response' => $this->readFixture("GET_process_spontankunde.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/process/100632/',
                    'parameters' => ['initiator' => 'admin'],
                    'response' => $this->readFixture("GET_process_spontankunde.json")
                ]
            ]
        );
        $response = $this->render(['id' => '100632'], $this->parameters, []);
        $this->assertStringContainsString(
            'Der Vorgang mit der Nummer 6 wurde erfolgreich entfernt.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Process\ProcessDeleteFailed';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/process/100044/',
                    'parameters' => ['initiator' => 'admin'],
                    'exception' => $exception
                ]
            ]
        );
        $this->render(['id' => 100044], $this->parameters, []);
    }
}
