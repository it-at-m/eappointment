<?php

namespace BO\Zmsticketprinter\Tests;

class HomeTest extends Base
{

    protected $classname = "Home";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        \BO\Zmsclient\Ticketprinter::setHomeUrl("http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/");
        $response = $this->render([ ], [ ], [ ]);
        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        $this->assertEquals($homeUrl, 'http://service.berlin.de/terminvereinbarung/ticketprinter/scope/141/');
    }

    public function testFailed()
    {
        \BO\Zmsclient\Ticketprinter::setHomeUrl("");
        $this->setExpectedException('\BO\Zmsticketprinter\Exception\HomeNotFound');
        $response = $this->render([ ], [ ], [ ]);
    }
}
