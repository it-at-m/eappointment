<?php

namespace BO\Zmsadmin\Tests;

class PickupKeyboardTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "PickupKeyboard";

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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('pickup-keyboard-handheld', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
