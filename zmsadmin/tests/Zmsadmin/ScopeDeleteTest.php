<?php

namespace BO\Zmsadmin\Tests;

class ScopeDeleteTest extends Base
{
    protected $arguments = [
        'id' => 141
    ];

    protected $parameters = [];

    protected $classname = "ScopeDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/owner/?success=scope_deleted');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
