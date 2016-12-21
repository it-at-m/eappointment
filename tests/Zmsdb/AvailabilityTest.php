<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Availability as Query;
use \BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Helper\DateTime;

class AvailabilityTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(21202, 0); //check without cache

        $this->assertEntity("\\BO\\Zmsentities\\Availability", $entity);
        $this->assertTrue((bool)$entity->weekday['saturday']);

        $entity = $query->readEntity(21202, 0, true); //check cache
        $this->assertTrue((bool)$entity->weekday['saturday']);
    }


    public function testEndAvailability()
    {
        $query = new Query();
        $entity = $query->readEntity(64054, 0); //Egon-Erwin-Kisch scope#135 for monday
        $dateTime = new DateTime("2016-05-30 10:00");
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $this->assertFalse(
            $entity->hasDate($dateTime, $now),
            "Scope should not be open, cause it has only 56 days bookable from now on"
        );
    }

    public function testReadList()
    {
        $query = new Query();
        $collection = $query->readList(109, 1); //by scope Helle Mitte
        $this->assertEntityList("\\BO\\Zmsentities\\Availability", $collection);
        $this->assertEquals(true, $collection->hasEntity('99755'));
        $this->assertEquals(
            $collection->withType('appointment')->count(),
            $collection->withType('openinghours')->count(),
            'Should have the same count'
        );
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $input->weekday['thursday'] = true;
        $input->weekday['friday'] = true;
        $entity = $query->writeEntity($input);
        $lastInsertedId = $entity->id;

        $entity = $query->readEntity($lastInsertedId, 1);
        $this->assertEquals(12, $entity->slotTimeInMinutes);
        $this->assertTrue((bool)$entity->weekday['thursday']);
        $this->assertTrue((bool)$entity->weekday['friday']);

        $entity->slotTimeInMinutes = 10;
        $entity = $query->updateEntity($lastInsertedId, $entity);
        $this->assertEquals(10, $entity->slotTimeInMinutes);

        $deleteTest = $query->deleteEntity($lastInsertedId);
        $this->assertTrue($deleteTest, "Failed to delete Availability from Database.");
    }

    public function testWriteOpeningHour()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $input->weekday['thursday'] = true;
        $input->weekday['friday'] = true;
        $input->type = 'openinghours';
        $entity = $query->writeEntity($input);
        $lastInsertedId = $entity->id;

        $entity = $query->readEntity($lastInsertedId, 1);
        $this->assertEquals('openinghours', $entity->type);
    }

    protected function getTestEntity()
    {
        return (new Entity())->createExample();
    }
}
