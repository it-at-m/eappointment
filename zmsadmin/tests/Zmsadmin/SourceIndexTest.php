<?php

namespace BO\Zmsadmin\Tests;

class SourceIndexTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "SourceIndex";

    public function testRendering()
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
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_sourcelist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Liste der verfügbaren Mandanten', (string)$response->getBody());
        $this->assertStringContainsString('href="/source/unittest/"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoSuperuser()
    {
        $this->expectException('\BO\Zmsadmin\Exception\NotAllowed');
        $this->expectExceptionCode(403);
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
