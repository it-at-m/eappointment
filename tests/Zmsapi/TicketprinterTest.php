<?php

namespace BO\Zmsapi\Tests;

class TicketprinterTest extends Base
{
    protected $classname = "Ticketprinter";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "enabled": true,
                "hash": "ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2",
                "id": 1,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links",
                "buttons": [
                    {
                        "type": "scope",
                        "url": "/scope/101/",
                        "scope": {
                            "id": 141
                        },
                        "enabled": true,
                        "name": "Bürgeramt Heerstraße"
                    },
                    {
                        "type": "cluster",
                        "url": "/cluster/110/",
                        "cluster": {
                            "id": 110
                        },
                        "enabled": true,
                        "name": "Bürgeramt Hohenzollerndamm"
                    },
                    {
                        "type": "link",
                        "url": "http://www.berlin.de/",
                        "scope": {
                            "id": 110
                        },
                        "enabled": true,
                        "name": "Portal berlin.de"
                    }
                ]
            }'
        ], []);
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFromButtonList()
    {
        $response = $this->render([], [
            '__body' => '{
                "buttonlist": "s141,c110,l[http://www.berlin.de/|Portal+Berlin.de]",
                "enabled": true,
                "hash": "ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2",
                "id": 1,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidInput()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "buttonlist": {}
            }',
        ], []);
    }

    public function testUnvalidHash()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterHashNotValid');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "buttonlist": "s141,c110,l[http://www.berlin.de/|Portal+Berlin.de]",
                "hash": "ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2",
                "id": 1234
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->setExpectedException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "buttonlist": "s141,c110,l[http://www.berlin.de/|Portal+Berlin.de]",
                "hash": "1234567890098765432"
            }'
        ], []);
    }
}
