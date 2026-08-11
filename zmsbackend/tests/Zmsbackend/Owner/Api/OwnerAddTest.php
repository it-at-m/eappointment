<?php

namespace BO\Zmsbackend\Tests\Owner\Api;

class OwnerAddTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "OwnerAdd";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction');
        $response = $this->render([], [
            '__body' => '{
                  "name": "Test Owner",
                  "url": "",
                  "contact": {
                      "street": "Test Street"
                  }
              }'
        ], []);
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertStringContainsString('"name":"Test Owner"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidOwner()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('jurisdiction');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{"extraField": 0}'
        ], []);
    }
}
