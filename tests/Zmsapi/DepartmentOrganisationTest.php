<?php

namespace BO\Zmsapi\Tests;

class DepartmentOrganisationTest extends Base
{
    protected $classname = "DepartmentOrganisation";

    public function testRendering()
    {
        $response = $this->render([72], [], []); //BA Egon-Erwin-Kisch-Str.
        $this->assertContains('Lichtenberg', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
