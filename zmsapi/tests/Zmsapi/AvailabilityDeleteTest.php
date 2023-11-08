<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsdb\Availability as Query;

class AvailabilityDeleteTest extends Base
{
    protected $classname = "AvailabilityDelete";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();
        $response = $this->render(['id' => $entity->getId()], [], []); //Test Availability
        $this->assertStringContainsString('startDate', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 1], [], []);
        $this->assertStringContainsString('availability.json","id":1', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
