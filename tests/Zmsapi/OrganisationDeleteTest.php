<?php

namespace BO\Zmsapi\Tests;

class OrganisationDeleteTest extends Base
{
    protected $classname = "OrganisationDelete";

    public function testRendering()
    {
        $response = $this->render([80], [], []); //Test Organisation
        $this->assertContains('Test Organisation', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
