<?php

namespace BO\Zmsadmin\Tests;

class PickupNotificationTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 82252
    ];

    protected $classname = "PickupNotification";

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
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            '
            Der Termin mit der Nummer 82252 wurde zur Abholung per SMS informiert',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
