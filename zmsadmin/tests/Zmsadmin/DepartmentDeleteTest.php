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
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/owner/?success=department_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
