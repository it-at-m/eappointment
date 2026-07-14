<?php

namespace BO\Zmsbackend\Tests\Availability\Api;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsbackend\Availability\Service\Availability as Query;

class AvailabilityGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "AvailabilityGet";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation();
        $response = $this->render(['id' => $entity->getId()], [], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsbackend\Availability\Exception\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }
}
