<?php

namespace BO\Zmsapi\Tests;

class DepartmentDeleteTest extends Base
{
    protected $classname = "DepartmentDelete";

    public function testRendering()
    {
        $response = $this->render([999], [], []); //Ordnungsamt Charlottenburg
        $this->assertContains('Test Department', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
