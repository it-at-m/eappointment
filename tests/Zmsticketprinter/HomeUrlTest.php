<?php

namespace BO\Zmsticketprinter\Tests;

class HomeUrlTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/scope/141/organisation/',
                'parameters' => ['resolveReferences' => 2],
                'response' => $this->readFixture("GET_organisation_71.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/ticketprinter/71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2/',
                'response' => $this->readFixture("GET_ticketprinter.json"),
            ],
            [
                'function' => 'readPostResult',
                'url' => '/ticketprinter/',
                'response' => $this->readFixture("GET_ticketprinter_buttonlist_multi.json"),
            ]
        ];
    }

    public function testRendering()
    {
        \BO\Zmsclient\Ticketprinter::setHomeUrl("");
        $this->render([ ], [
            '__cookie' => [
                'Ticketprinter' => '71ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2',
            ],
            'ticketprinter' => [
                'buttonlist' => 's141,c110,l[http://www.berlin.de/|Portal berlin.de]'
            ],
            'home' => 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/'
        ], [ ]);
        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        $this->assertEquals($homeUrl, 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/');
    }
}
