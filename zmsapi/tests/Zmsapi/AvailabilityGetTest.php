<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsdb\Availability as Query;

class AvailabilityGetTest extends Base
{
    protected $classname = "AvailabilityGet";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
        $entity = (new Query())->writeEntity($input);
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $response = $this->render(['id' => $entity->getId()], [], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->permissions['availability'] = true;
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 1], [], []);
    }

    public function testMissingAvailabilityPermissionThrows403()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $this->render(['id' => 1], [], []);
    }
}
