<?php

namespace BO\Zmsapi\Tests;

class OrganisationHashTest extends Base
{
    protected $classname = "OrganisationHash";

    public function testRendering()
    {
        $response = $this->render([54], [], []); //Pankow
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertContains('"hash":"54', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRenderingDisabled()
    {
        $response = $this->render([65], [], []); //Friedrichshain-Kreuzberg mit kioskpasswortschutz
        $this->assertContains('ticketprinter.json', (string)$response->getBody());
        $this->assertContains('"enabled":false', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
