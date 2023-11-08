<?php

namespace BO\Zmsadmin\Tests;

class PickupMailTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 82252
    ];

    protected $classname = "PickupMail";

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
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
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
                    'url' => '/mails/',
                    'response' => $this->readFixture("POST_mail.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            '
            Der Termin mit der Nummer 82252 wurde zur Abholung per E-Mail informiert',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithoutEmailFrom()
    {
        $this->expectException('\BO\Zmsadmin\Exception\MailFromMissing');
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
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_process_without_emailfrom.json")
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
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
