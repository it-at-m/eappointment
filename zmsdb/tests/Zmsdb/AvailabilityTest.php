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
        $entity = $query->readEntity(21202, 0, true); //entity is not loaded -> no cache usage
        $this->assertEntity("\\BO\\Zmsentities\\Availability", $entity);
        $this->assertTrue((bool)$entity->weekday['saturday']);

        $entity = $query->readEntity(21202, 0, true); //check cache
        $this->assertTrue((bool)$entity->weekday['saturday']);

        //without real resolving, returns entity as it is, just for test method
        $entityResolved = $query->readResolvedReferences($entity, 0);
        $this->assertEntity("\\BO\\Zmsentities\\Availability", $entityResolved);
    }


    public function testEndAvailability()
    {
        $query = new Query();
        $entity = $query->readEntity(64054, 2); //Egon-Erwin-Kisch scope#135 for monday
        $dateTime = new DateTime("2016-05-30 10:00");
        $this->assertFalse(
            $entity->hasDate($dateTime, static::$now),
            "Scope should not be open, cause it has only 56 days bookable from now on"
        );
    }

    public function testReadList()
    {
        $startDate = new \DateTimeImmutable("2016-05-1 00:00");
        $endDate = new \DateTimeImmutable("2016-05-31 23:59");
        $query = new Query();
        $collection = $query->readList(109, 1, $startDate, $endDate); //by scope Helle Mitte
        $this->assertEntityList("\\BO\\Zmsentities\\Availability", $collection);
        $this->assertEquals(true, $collection->hasEntity('99755'));
        $this->assertEquals(
            $collection->withType('appointment')->count(),
            $collection->withType('openinghours')->count(),
            'Should have the same count'
        );
        $this->assertEquals(18, $collection->count());
    }

    public function testReadListSkipOld()
    {
        $now = new \DateTimeImmutable("2016-07-01 11:55");
        $query = new Query();
        $scope = (new \BO\Zmsdb\Scope)->readEntity(109);

        $collection = $query->readAvailabilityListByScope($scope, 1); //by scope Helle Mitte
        $this->assertEquals(9, $collection->count());

        $collection = $query->readAvailabilityListByScope($scope, 1, $now); //by scope Helle Mitte
        $this->assertEquals(0, $collection->count());
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
        $this->assertEquals(10, $entity->slotTimeInMinutes);
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
