<?php

namespace BO\Zmsticketprinter\Tests;

class TicketprinterByScopeWithNotificationTest extends Base
{
    protected $classname = "TicketprinterByScope";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"), //Treptow Köpenick
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/711abcdefghijklmnopqrstuvwxyz/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_notification.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/department/',
                'response' => $this->readFixture("GET_department_127.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/queue/',
                'response' => $this->readFixture("GET_queuelist_141.json"), //Bürgeramt 1 in Köpenick
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
            'scopeId' => 141
        ], [
            '__cookie' => [
                'Ticketprinter' => '711abcdefghijklmnopqrstuvwxyz',
            ]
        ], [ ]);
        $this->assertStringContainsString('Wartenummer für', (string) $response->getBody());
        $this->assertStringContainsString('Heerstraße', (string) $response->getBody());
        $this->assertStringContainsString('Handynummer nachträglich eintragen', (string) $response->getBody());
    }
}
