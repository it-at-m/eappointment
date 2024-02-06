<?php

namespace BO\Zmsticketprinter\Tests;

class HomeTest extends Base
{

    protected $classname = "Home";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $request = static::createBasicRequest('GET', '/');
        $url = 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/';
        \BO\Zmsclient\Ticketprinter::setHomeUrl($url, $request);
        $response = $this->render([ ], [ ], [ ]);
        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        $this->assertEquals($homeUrl, 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/');
    }

    public function testFailed()
    {
        $request = static::createBasicRequest('GET', '/');
        \BO\Zmsclient\Ticketprinter::setHomeUrl('', $request);
        $this->expectException('\BO\Zmsticketprinter\Exception\HomeNotFound');
        $response = $this->render([ ], [ ], [ ]);
    }
}
