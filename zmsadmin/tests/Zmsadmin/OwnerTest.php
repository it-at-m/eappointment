<?php

namespace BO\Zmsadmin\Tests;

class OwnerTest extends Base
{
    protected $arguments = [
        'id' => 23
    ];

    protected $parameters = [];

    protected $classname = "Owner";

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
                    'url' => '/owner/23/',
                    'response' => $this->readFixture("GET_owner.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Kundeneinrichtung und -administration', (string)$response->getBody());
        $this->assertStringContainsString('Berlin', (string)$response->getBody());
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
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/23/',
                    'response' => $this->readFixture("GET_owner.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/owner/23/',
                    'response' => $this->readFixture("GET_owner.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Berlin',
            'contact' => array(
                'street' => 'Berlin'
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/owner/23/?success=owner_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
