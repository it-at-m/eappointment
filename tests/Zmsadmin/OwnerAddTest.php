<?php

namespace BO\Zmsadmin\Tests;

class OwnerAddTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "OwnerAdd";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Kundeneinrichtung und -administration', (string)$response->getBody());
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
                    'function' => 'readPostResult',
                    'url' => '/owner/add/',
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
        $this->assertRedirect($response, '/owner/23/?success=owner_created');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
