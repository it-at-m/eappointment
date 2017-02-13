<?php

namespace BO\Zmsapi\Tests;

class OrganisationGetTest extends Base
{
    protected $classname = "OrganisationGet";

    public function testRendering()
    {
        $response = $this->render([54], [], []); //Pankow
        $this->assertContains('ac9df1f2983c3f94aebc1a9bd121bfecf5b374f2', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $response = $this->render([], [], []);
    }

    public function testOrganisationNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Organisation\OrganisationNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render([53], [], []);
    }
}
