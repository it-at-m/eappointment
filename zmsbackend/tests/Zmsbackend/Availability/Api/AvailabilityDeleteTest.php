<?php

namespace BO\Zmsbackend\Tests\Availability\Api;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsbackend\Availability\Service\Availability as Query;

class AvailabilityDeleteTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "AvailabilityDelete";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('availability');
        $response = $this->render(['id' => $entity->getId()], [], []); //Test Availability
        $this->assertStringContainsString('startDate', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('availability');
        $response = $this->render(['id' => 1], [], []);
        $this->assertStringContainsString('availability.json","id":1', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
