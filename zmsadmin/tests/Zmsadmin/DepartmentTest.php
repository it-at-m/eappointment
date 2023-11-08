<?php

namespace BO\Zmsadmin\Tests;

class DepartmentTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [];

    protected $classname = "Department";

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
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Bürgeramt - Behörde: Einrichtung und Administration', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
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
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/department/74/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Bürgeramt',
            'contact' => array(
                'street' => 'Otto-Suhr-Allee 100, 10585 Berlin',
                'name' => 'Fr. Krause-Jentsch',
            ),
            'email' => 'test@example.com',
            'preferences' => array(
                'notifications' => array(
                    'enabled' => '1',
                    'identification' => 'test@example.com',
                    'sendConfirmationEnabled' => '1',
                    'sendReminderEnabled' => '1',
                ),
            ),
            'links' => array(
                array(
                  'name' => 'Bürgerämter Charlottenburg-Wilmersdorf',
                  'url' => 'http://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html',
                  'target' => '1',
                ),
                array(
                  'name' => '',
                  'url' => '',
                ),
            ),
            'dayoff' => array(
                array(
                  'name' => '',
                  'date' => '01.04.2016',
                ),
            ),
            'save' => 'save',
            'sendEmailReminderMinutesBefore' => 10,
            'sendEmailReminderEnabled' => false,
        ], [], 'POST');
        $this->assertRedirect($response, '/department/74/?success=department_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
