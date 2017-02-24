<?php

namespace BO\Zmsticketprinter\Tests;

class TicketprinterByScopeWithoutHashTest extends Base
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
                'response' => $this->readFixture("GET_organisation_78.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/organisation/78/hash/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_single.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/workstationcount/',
                'response' => $this->readFixture("GET_scope_312.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/scope/312/queue/',
                'response' => $this->readFixture("GET_queuelist_312.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $this->render([
            'scopeId' => 312
        ], [ ], [ ]);
        $this->assertTrue('71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2' == $_COOKIE['Ticketprinter']);
    }
}
