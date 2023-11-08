<?php

namespace BO\Zmsapi\Tests;

class OwnerAddOrganisationTest extends Base
{
    protected $classname = "OwnerAddOrganisation";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => 23], [
            '__body' => '{
                  "name": "Test Organisation"
              }'
        ], []);
        $this->assertStringContainsString('organisation.json', (string)$response->getBody());
        $this->assertStringContainsString('"name":"Test Organisation"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidOrganisation()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(['id' => 23], [
            '__body' => '{"extraField": 0}'
        ], []);
    }
}
