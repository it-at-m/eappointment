<?php

namespace BO\Zmsapi\Tests;

class TicketprinterTest extends Base
{
    protected $classname = "Ticketprinter";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "buttonlist": "s141,c4,l[http://www.berlin.de/|Portal+Berlin.de]",
                "enabled": true,
                "hash": "65d88282b9a15d355af1dd619cb86e057c",
                "id": 1234,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertContains('"hash":"65d88282b9a15d355af1dd619cb86e057c', (string)$response->getBody());
        $this->assertContains('"type":"scope"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidInput()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }
}
