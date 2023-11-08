<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsdb\Availability as Query;

class AvailabilityUpdateTest extends Base
{
    protected $classname = "AvailabilityUpdate";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();
        $response = $this->render(["id"=> $entity->getId()], [
            '__body' => '{
                  "id": '. $entity->getId() .',
                  "description": "",
                  "scope": {
                      "id": 312
                  }
              }'
        ], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id"=> 1], [
            '__body' => '{
                  "id": 1,
                  "description": "Test Öffnungszeit not found",
                  "scope": {
                      "id": 312
                  }
              }'
        ], []);
    }
}
