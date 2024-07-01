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
                "hash": "456abcdefghijklmnopqrstuvwxyz",
                "id": 456,
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
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFromButtonList()
    {
        $response = $this->render([], [
            '__body' => '{
                "buttonlist": "s141,l[http://www.berlin.de/|Portal+Berlin.de]",
                "enabled": true,
                "hash": "456abcdefghijklmnopqrstuvwxyz",
                "id": 456,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testFromButtonListScopeFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Ticketprinter\UnvalidButtonList');
        $this->expectExceptionCode(428);
        $this->render([], [
            '__body' => '{
                "buttonlist": "s139",
                "enabled": true,
                "hash": "54abcdefghijklmnopqrstuvwxyz",
                "id": 1,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
    }

    public function testMatchingFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\UnvalidButtonList');
        $this->expectExceptionCode(428);
        $this->render([], [
            '__body' => '{
                "buttonlist": "s106",
                "enabled": true,
                "hash": "54abcdefghijklmnopqrstuvwxyz",
                "id": 4,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
    }

    public function testEnableTicketprinter()
    {
        $response = $this->render([], [
            '__body' => '{
                "buttonlist": "s141",
                "enabled": true,
                "hash": "456abcdefghijklmnopqrstuvwxyz",
                "id": 456,
                "lastUpdate": 1447925326000,
                "name": "Eingangsbereich links"
            }'
        ], []);
        $this->assertStringContainsString('"enabled":true', (string)$response->getBody());
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
                "buttonlist": "s141,l[http://www.berlin.de/|Portal+Berlin.de]",
                "hash": "54abcdefghijklmnopqrstuvwxyz",
                "id": 1234
            }'
        ], []);
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
                "buttonlist": "s141,l[http://www.berlin.de/|Portal+Berlin.de]",
                "hash": "1234567890098765432"
            }'
        ], []);
    }
}
