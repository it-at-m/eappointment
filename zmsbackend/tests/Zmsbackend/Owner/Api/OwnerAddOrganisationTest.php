<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

class OwnerAddOrganisationTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OwnerAddOrganisation";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('organisation')
            ->setRights('organisation')
            ->addDepartment([
                'id' => 96 // Bürgeramt, Treptow-Köpenick (owner Berlin #23)
            ]);
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
        $this->setWorkstation()->getUseraccount()->setPermissions('organisation')
            ->setRights('organisation')
            ->addDepartment([
                'id' => 96
            ]);
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(['id' => 23], [
            '__body' => '{"extraField": 0}'
        ], []);
    }
}
