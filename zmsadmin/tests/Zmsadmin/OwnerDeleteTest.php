<?php

namespace BO\Zmsadmin\Tests;

class OwnerDeleteTest extends Base
{
    protected $arguments = [
        'id' => 23
    ];

    protected $parameters = [];

    protected $classname = "OwnerDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/owner/23/',
                    'response' => $this->readFixture("GET_owner.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/owner/?success=owner_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
