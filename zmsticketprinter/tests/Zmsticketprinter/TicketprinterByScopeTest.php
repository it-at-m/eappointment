<?php

namespace BO\Zmsticketprinter\Tests;

class TicketprinterByScopeTest extends Base
{
    protected $classname = "TicketprinterByScope";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_78.json"), //Treptow Köpenick
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/78abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/department/',
                'response' => $this->readFixture("GET_department_127.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/queue/',
                'response' => $this->readFixture("GET_queuelist_312.json"), //Bürgeramt 1 in Köpenick
            ],
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'parameters' => [],
                'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                'response' => $this->readFixture("GET_config.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
            'scopeId' => 312
        ], [
            '__cookie' => [
                'Ticketprinter' => '78abcdefghijklmnopqrstuvwxyz',
            ]
        ], [ ]);
        $this->assertStringContainsString('Apparat-Id: 71abcdefghijklmnopqrstuvwxyz', (string) $response->getBody());
        $this->assertStringContainsString('Köpenick', (string) $response->getBody());
        $this->assertStringNotContainsString('Handynummer nachträglich eintragen', (string) $response->getBody());
    }
}
