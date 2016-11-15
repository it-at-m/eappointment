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
                'url' => '/organisation/scope/141/',
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/organisation/71/hash/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([
            'id' => 141
        ], [ ], [ ]);
        $this->assertTrue('71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2' == $_COOKIE['Ticketprinter']);
    }
}
