<?php

namespace BO\Zmsadmin\Tests;

class UseraccountDeleteTest extends Base
{
    protected $arguments = [
        'loginname' => 'testuser'
    ];

    protected $parameters = [];

    protected $classname = "UseraccountDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [

                [
                    'function' => 'readDeleteResult',
                    'url' => '/useraccount/testuser/',
                    'response' => $this->readFixture("GET_useraccount_testuser.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/useraccount/?success=useraccount_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
