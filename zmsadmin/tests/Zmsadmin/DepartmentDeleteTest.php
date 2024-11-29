<?php

namespace BO\Zmsadmin\Tests;

class DepartmentDeleteTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [];

    protected $classname = "DepartmentDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/department/74/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [

                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('/owner/?success=department_deleted', $response->getHeaderLine('Location'));
        $this->assertEquals(302, $response->getStatusCode());
    }
}
