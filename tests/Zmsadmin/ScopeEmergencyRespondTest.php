<?php

namespace BO\Zmsadmin\Tests;

class ScopeEmergencyRespondTest extends Base
{
    protected $arguments = [
        'id' => 141
    ];

    protected $parameters = [];

    protected $classname = "ScopeEmergencyResponse";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/emergency/respond/',
                    'response' => $this->readFixture("GET_scope_141_emergency_accepted.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('"acceptedByWorkstation":"14"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
