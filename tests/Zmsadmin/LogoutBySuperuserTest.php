<?php

namespace BO\Zmsadmin\Tests;

class LogoutBySuperuserTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [
        'workstation' => array('useraccount' => array('id' => 'testuser'))
    ];

    protected $classname = "LogoutBySuperuser";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/login/testuser/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/department/74/useraccount/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
