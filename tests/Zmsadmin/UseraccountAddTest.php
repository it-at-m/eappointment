<?php

namespace BO\Zmsadmin\Tests;

class UseraccountAddTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'id' => 'unittest',
        'changePassword' => array(
            'passwort',
            'passwort',
        ),
        'departments' => array(
            ['id' => 74],
            ['id' => 57],
        ),
        'rights' => array(
            'sms' => '1',
            'ticketprinter' => '1',
            'availability' => '1',
            'scope' => '1'
        ),
        'save' => 'save'
    ];

    protected $classname = "UseraccountAdd";

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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Nutzer: Einrichtung und Administration', (string)$response->getBody());
        $this->assertContains('Nutzer anlegen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/useraccount/',
                    'response' => $this->readFixture("GET_useraccount_unittest.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/useraccount/?success=useraccount_created');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
