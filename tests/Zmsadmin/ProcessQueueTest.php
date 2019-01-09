<?php

namespace BO\Zmsadmin\Tests;

class ProcessQueueTest extends Base
{
    protected $arguments = [
        'date' => '2016-04-01'
    ];

    protected $parameters = [
        'selectedprocess' => 82252,
        'print' => 1
    ];

    protected $classname = "ProcessQueue";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Ihr Termin ist am', (string)$response->getBody());
        $this->assertContains('Fr. 01. April 2016 um', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithoutAppointment()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/waitingnumber/',
                    'response' => $this->readFixture("GET_process_queued.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/confirmation/mail/',
                    'response' => $this->readFixture("POST_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/confirmation/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'familyName' => 'Test BO',
            'telephone' => '1234567890',
            'email' => 'zmsbo@berlinonline.de',
            'sendConfirmation' => 1,
            'sendMailConfirmation' => 1,
            'headsUpTime' => 3600,
            'requests' => [120703]
        ], [], 'POST');
        $this->assertRedirect($response, '/appointmentForm/?selectedprocess=100011&success=process_queued');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingNotOpened()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 19:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/process/waitingnumber/',
                    'response' => $this->readFixture("GET_process_queued.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/confirmation/mail/',
                    'response' => $this->readFixture("POST_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/confirmation/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'familyName' => 'Test BO',
            'telephone' => '1234567890',
            'email' => 'zmsbo@berlinonline.de',
            'sendConfirmation' => 1,
            'sendMailConfirmation' => 1,
            'headsUpTime' => 3600,
            'requests' => [120703]
        ], [], 'POST');
        $this->assertRedirect($response, '/appointmentForm/?selectedprocess=100011&success=process_queued');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
