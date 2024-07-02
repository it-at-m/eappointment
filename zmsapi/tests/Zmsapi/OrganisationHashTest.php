<?php

namespace BO\Zmsapi\Tests;

class OrganisationHashTest extends Base
{
    protected $classname = "OrganisationHash";

    public function testRendering()
    {
        $response = $this->render(['id' => 54], [], []); //Pankow
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertStringContainsString('"hash":"54', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithName()
    {
        $response = $this->render(['id' => 54], ['name' => ''], []); //Pankow
        $this->assertStringContainsString('"name":"Ticket Printer for Pankow"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testTicketprinterDisabled()
    {
        $response = $this->render(['id' => 65], [], []); //Friedrichshain-Kreuzberg mit kioskpasswortschutz
        $this->assertStringContainsString('ticketprinter.json', (string)$response->getBody());
        $this->assertStringContainsString('"enabled":false', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testOrganisationNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Organisation\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
