<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Availability as Query;
use \BO\Zmsentities\Availability as Entity;

class AvailabilityTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(21202, 0); //check without cache

        $this->assertEntity("\\BO\\Zmsentities\\Availability", $entity);
        $this->assertEquals(64, $entity->weekday['saturday']);

        $entity = $query->readEntity(21202, 0, true); //check cache
        $this->assertEquals(64, $entity->weekday['saturday']);
    }

    public function testReadList()
    {
        $query = new Query();
        $collection = $query->readList(141); //by scope Heerstraße
        $this->assertEntityList("\\BO\\Zmsentities\\Availability", $collection);
        $this->assertEquals(true, $collection->hasEntity('68985')); //2016-01-28 until 2016-05-22
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $lastInsertedId = $query->writeEntity($input);

        $entity = $query->readEntity($lastInsertedId, 1);
        $this->assertEquals(12, $entity->slotTimeInMinutes);

        $entity->slotTimeInMinutes = 10;
        $entity = $query->updateEntity($lastInsertedId, $entity);
        $this->assertEquals(10, $entity->slotTimeInMinutes);

        $deleteTest = $query->deleteEntity($lastInsertedId);
        $this->assertTrue($deleteTest, "Failed to delete Availability from Database.");
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->createExample();
    }
}
