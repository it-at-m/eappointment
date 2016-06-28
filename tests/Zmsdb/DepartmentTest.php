<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Department as Query;
use \BO\Zmsentities\Department as Entity;

class DepartmentTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 75);
        $this->assertEntity("\\BO\\Zmsentities\\Department", $entity);
        $this->assertEquals('service@berlinonline.de', $entity->email);
        $this->assertEquals(true, $entity->hasNotificationEnabled());

        $entity->email = "max@berlinonline.de";
        $entity->setNotificationPreferences(false);
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals('max@berlinonline.de', $entity->email);
        $this->assertEquals(false, $entity->hasNotificationEnabled());

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Department from Database.");
    }

    protected function getTestEntity()
    {
        return $input = new Entity(array(
            'email' => 'service@berlinonline.de',
            'preferences' => [
                'notifications' => [
                    'enabled' => true,
                    'identification' => 'service@berlinonline.de',
                    'sendConfirmationEnabled' => false,
                    'sendReminderEnabled' => false
                ]
            ],
            'name' => 'Muster Bürgeramt',
            'contact' => [
                'country' => 'Germany',
                'name' => 'Max Mustermann',
                'postalCode' => '',
                'region' => '',
                'street' => 'Musterallee 1, 10178 Berlin',
                'streetNumber' => ''
            ]
        ));
    }
}
