<?php

namespace BO\Zmsadmin\Tests;

class TicketprinterConfigTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "TicketprinterConfig";

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
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'parameters' => ['resolveReferences' => 5],
                    'response' => $this->readFixture("GET_organisation_71_resolved5.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture('GET_source.json')
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'Anmeldung an Warteschlange',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Charlottenburg-Wilmersdorf',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Dienstleistung A',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'Dienstleistung B',
            (string)$response->getBody()
        );
        $this->assertStringContainsString('data-ticketprinter-config', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
