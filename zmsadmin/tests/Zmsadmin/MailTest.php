<?php

namespace BO\Zmsadmin\Tests;

class MailTest extends Base
{
    protected $selectedProcess = 82252;

    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 82252,
        'subject' => 'This is a test subject',
        'message' => 'This is a test body message',
        'submit' => 'form'
    ];

    protected $classname = "Mail";

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
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("POST_mail.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/mail/?success=mail_sent&selectedprocess='. $this->selectedProcess);
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [], []);
        $this->assertStringContainsString('message-error', (string)$response->getBody());
        $this->assertStringContainsString(
            'Für einen E-Mail Versand muss eine gültige E-Mail-Adresse eingetragen sein.',
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
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/mails/',
                    'response' => $this->readFixture("POST_mail.json")
                ]
            ]
        );
        $parameters = array_merge(['dialog' => 1], $this->parameters);
        $response = $this->render($this->arguments, $parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/mail/?success=mail_sent&selectedprocess='. $this->selectedProcess .'&dialog=1'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testWithoutEmailFrom()
    {
        $this->expectException('\BO\Zmsadmin\Exception\MailFromMissing');
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
                    'response' => $this->readFixture("GET_process_without_emailfrom.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, [], 'POST');
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
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'selectedprocess' => 82252,
            'subject' => 'This is a test subject',
            'message' => '',
            'submit' => 'form'
        ], [], 'POST');
        $this->assertStringContainsString('has-error', (string)$response->getBody());
        $this->assertStringContainsString('Es muss eine aussagekräftige Nachricht eingegeben werden', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
