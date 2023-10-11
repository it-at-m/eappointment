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
        $entity = $query->readEntity($entity->id, 2);

        $this->assertNotEmpty($entity);
        $this->assertEntity("\\BO\\Zmsentities\\Department", $entity);
        $this->assertEquals('service@berlinonline.de', $entity->email);

        $this->assertEquals(1, count($entity->links));
        $this->assertFalse(null === $entity->dayoff->getEntityByName('Test Feiertag'));
   
        $this->assertEquals(0, $entity->getNotificationPreferences()['enabled']);
        $this->assertEquals(0, $entity->getNotificationPreferences()['sendConfirmationEnabled']);
        $this->assertEquals(0, $entity->getNotificationPreferences()['sendReminderEnabled']);

        $entity->preferences['notifications']['enabled'] = 1;
        $entity->email = "max@berlinonline.de";
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals(1, $entity->getNotificationPreferences()['enabled']);
        $this->assertEquals('max@berlinonline.de', $entity->email);
    }

    public function testDeleteWithChildren()
    {
        $this->expectException('\BO\Zmsdb\Exception\Department\ScopeListNotEmpty');
        $query = new Query();
        $this->assertFalse($query->deleteEntity(72)); //Bürgeramt Egon-Erwin-Kirsch-Straße
    }

    public function testDeleteWithoutChildren()
    {
        $query = new Query();
        $this->assertStringContainsString('Test Department', $query->deleteEntity(999)); //Test Department
    }

    public function testReadWithDayOff()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 75);
        $entity = $query->readEntity($entity->id, 2, true);
        $this->assertEquals(1, count($entity->dayoff));
    }

    public function testReadList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $collection = $query->readList();
        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Department", $collection);
        $this->assertEquals(true, $collection->hasEntity('72')); //Bürgeramt Egon-Erwin-Kirsch-Straße
        $this->assertEquals(true, $collection->hasEntity('123')); //Test Entity exists
    }

    public function testReadEntityFailed()
    {
        $query = new Query();
        $entity = $query->readEntity(99999);
        $this->assertEquals(null, $entity);
    }

    protected function getTestEntity()
    {
        return new Entity(array(
            'id' => 123,
            'email' => 'service@berlinonline.de',
            'sendEmailReminderEnabled' => 1,
            'sendEmailReminderMinutesBefore' => 5,
            'preferences' => [
                'notifications' => [
                    'enabled' => false,
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
            ],
            'links' => [
                0 => [
                    'name' => 'Test Link',
                    'url' => 'https://service.berlin.de',
                    'target' => 1
                ]
            ],
            'dayoff' => [
                0 => [
                  "date" => 1459511700,
                  "name" => "Test Feiertag"
                ]
            ]
        ));
    }
}
