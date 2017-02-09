<?php

namespace BO\Zmsapi\Tests;

class ScopeDeleteTest extends Base
{
    protected $classname = "ScopeDelete";

    public function testRendering()
    {
        $response = $this->render([615], [], []); //Ordnungsamt Charlottenburg
        $this->assertContains('Ordnungsamt Charlottenburg-Wilmersdorf ', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
