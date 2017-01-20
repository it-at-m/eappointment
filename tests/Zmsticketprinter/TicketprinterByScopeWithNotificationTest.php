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
                'url' => '/organisation/scope/141/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"), //Treptow Köpenick
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single_notification.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/queue/',
                'response' => $this->readFixture("GET_queuelist_141.json"), //Bürgeramt 1 in Köpenick
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
            'scopeId' => 141
        ], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ]
        ], [ ]);
        $this->assertContains('Wartenummer für', (string) $response->getBody());
        $this->assertContains('Heerstraße', (string) $response->getBody());
        $this->assertContains('Handynummer nachträglich eintragen', (string) $response->getBody());
    }
}
