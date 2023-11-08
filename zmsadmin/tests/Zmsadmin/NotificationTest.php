<?php

namespace BO\Zmsadmin\Tests;

class NotificationTest extends Base
{
    protected $selectedProcess = 194104;

    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 194104,
        'message' => 'This is a test body message',
        'submit' => 'form'
    ];

    protected $classname = "Notification";

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
                    'url' => '/process/'. $this->selectedProcess .'/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/notification/?success=notification_sent&selectedprocess='. $this->selectedProcess
        );
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingWithoutProcess()
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
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('message-error', (string)$response->getBody());
        $this->assertStringContainsString(
            'Für einen SMS Versand muss eine gültige Telefonnummer eingetragen sein.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingInDialogWindow()
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
                    'url' => '/process/'. $this->selectedProcess .'/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $parameters = array_merge(['dialog' => 1], $this->parameters);
        $response = $this->render($this->arguments, $parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/notification/?success=notification_sent&selectedprocess='. $this->selectedProcess .'&dialog=1'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingReminder()
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
                    'url' => '/process/'. $this->selectedProcess .'/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $parameters = array_merge($this->parameters, ['status' => 'queued', 'submit' => 'reminder']);
        $response = $this->render($this->arguments, $parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/notification/?success=notification_sent&selectedprocess='. $this->selectedProcess
        );
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testFormFailed()
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
                    'url' => '/process/'. $this->selectedProcess .'/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'selectedprocess' => 194104,
            'subject' => 'This is a test subject',
            'message' => '',
            'submit' => 'form'
        ], [], 'POST');
        $this->assertStringContainsString('has-error', (string)$response->getBody());
        $this->assertStringContainsString('Es muss eine aussagekräftige Nachricht eingegeben werden', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
