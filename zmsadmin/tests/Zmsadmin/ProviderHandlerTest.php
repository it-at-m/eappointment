<?php

namespace BO\Zmsadmin\Tests;

class ProviderHandlerTest extends Base
{
    protected $arguments = ['source' => 'dldb'];

    protected $parameters = [];

    protected $classname = "\BO\Zmsadmin\Helper\ProviderHandler";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => true],
                    'response' => $this->readFixture("GET_providerlist_assigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => false],
                    'response' => $this->readFixture("GET_providerlist_notassigned.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertStringContainsString('assigned', (string)$response->getBody());
        $this->assertStringContainsString('notAssigned', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
