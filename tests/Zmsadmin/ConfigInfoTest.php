<?php

namespace BO\Zmsadmin\Tests;

class ConfigInfoTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "ConfigInfo";

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
                    'url' => '/config/',
                    'response' => $this->readFixture("GET_config.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('Konfiguration System', (string)$response->getBody());
        $this->assertContains("Sie sind in Kürze an der Reihe.", (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTemplatePath()
    {
        $path = \BO\Zmsadmin\Helper\TemplateFinder::getTemplatePath();
        $this->assertContains('src/Zmsadmin/Helper/../../../templates', $path);
    }
}
