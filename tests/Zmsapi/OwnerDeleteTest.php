<?php

namespace BO\Zmsapi\Tests;

class OwnerDeleteTest extends Base
{
    protected $classname = "OwnerDelete";

    public function testRendering()
    {
        $response = $this->render([99], [], []); //Test Owner
        $this->assertContains('Test Kunde', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
