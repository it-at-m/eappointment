<?php

namespace BO\Zmsticketprinter\Tests;

class CustomizedTemplateTest extends Base
{
    protected $classname = "TicketprinterByScope";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/710caa9f2e7547a52106d6b00868c5cf3a/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_615.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/workstationcount/',
                'response' => $this->readFixture("GET_scope_615.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/615/queue/',
                'response' => $this->readFixture("GET_queuelist_312.json"), //Bürgeramt 1 in Köpenick
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
            'scopeId' => 615
        ], [
            '__cookie' => [
                'Ticketprinter' => '710caa9f2e7547a52106d6b00868c5cf3a',
            ]
        ], [ ]);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains('Ordnungsamt Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertNotContains('Wartenummer für', (string) $response->getBody());
    }
}
