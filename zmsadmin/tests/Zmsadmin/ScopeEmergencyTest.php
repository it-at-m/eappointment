<?php

namespace BO\Zmsadmin\Tests;

class ScopeEmergencyTest extends Base
{
    protected $arguments = [
        'id' => 141
    ];

    protected $parameters = [];

    protected $classname = "ScopeEmergency";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/emergency/',
                    'response' => $this->readFixture("GET_scope_141_emergency_activated.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertStringContainsString('"activated":"1"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingStop()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/scope/141/emergency/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'GET');
        $this->assertStringContainsString('"activated":"0"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
