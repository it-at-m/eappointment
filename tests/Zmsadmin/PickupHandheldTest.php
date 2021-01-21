<?php

namespace BO\Zmsadmin\Tests;

class PickupHandheldTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "PickupHandheld";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('pickup-handheld-view', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithSelectedProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'inputNumber' => 82252
        ], [], 'POST');
        $this->assertContains('pickup-handheld-view', (string)$response->getBody());
        $this->assertContains('data-selected-process="82252"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithCalledProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_workstation_with_process_pickup.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'inputNumber' => 82252
        ], [], 'POST');
        $this->assertContains('pickup-handheld-view', (string)$response->getBody());
        $this->assertContains('data-selected-process="82252"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
