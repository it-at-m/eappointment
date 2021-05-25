<?php

namespace BO\Zmsadmin\Tests;

class DayoffTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Dayoff";

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
        $this->assertStringContainsString('Allgemein gültige Feiertage - Jahresauswahl', (string)$response->getBody());
        $this->assertStringContainsString('2026', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
