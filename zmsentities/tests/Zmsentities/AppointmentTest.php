<?php

namespace BO\Zmsentities\Tests;

class AppointmentTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Appointment';

    public $collectionclass = '\BO\Zmsentities\Collection\AppointmentList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertStringContainsString('123', (string)$entity);
    }

    public function testDate()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->availability = (new \BO\Zmsentities\Availability())->getExample();
        $time = $entity->getEndTime();
        $entity->addDate($time->getTimestamp());
        $entity->setTime('12:00');
        $this->assertTrue('Mittwoch 18. November 2015' == $entity->toDate(), 'German date does not match.');
        $this->assertTrue('Wednesday November 18, 2015' == $entity->toDate('en'), 'English date does not match.');
        $this->assertTrue('12:00 Uhr' == $entity->toTime(), 'German time does not match.');
        $this->assertTrue('12:00 o\'clock' == $entity->toTime('en'), 'English time does not match.');
        $entity->setDateByString('2016-05-27 11:50');
        $this->assertTrue('Freitag 27. Mai 2016' == $entity->toDate(), 'German date does not match.');
        $this->assertTrue('Friday May 27, 2016' == $entity->toDate('en'), 'English date does not match.');
        $this->assertTrue('11:50 Uhr' == $entity->toTime(), 'German time does not match.');
        $this->assertTrue('11:50 o\'clock' == $entity->toTime('en'), 'English time does not match.');
        try {
            $entity->setDateByString('2016/05/27 11:50');
            $this->fail("Expected exception DateStringWrongFormat not thrown");
        } catch (\BO\Zmsentities\Exception\DateStringWrongFormat $exception) {
            $this->assertEquals(500, $exception->getCode());
            return;
        }
        $this->fail("Expected exception (\BO\Zmsentities\Exception\DateStringWrongFormat) not thrown");
    }

    public function testScope()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addScope('141');
        $this->assertTrue('141' == $entity->scope['id'], 'Scope does not match.');
    }

    public function testSlotCount()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addSlotCount();
        $this->assertTrue(3 == $entity->slotCount, 'SlotCount not added.');
        $entity->addSlotCount(1);
        $this->assertTrue(1 == $entity->slotCount, 'SlotCount not added.');
    }

    public function testCalculatedSlotCount()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $entity2 = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $this->assertEquals(4, $collection->getCalculatedSlotCount());
    }

    public function testAvailability()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->availability = (new \BO\Zmsentities\Availability())->getExample();
        $availability = $entity->getAvailability();
        $this->assertEntity('\\BO\\Zmsentities\\Availability', $availability);
    }

    public function testMatching()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $entity2 = (new $this->entityclass())->getExample();
        $entity2->addScope('141');
        $collection->addEntity($entity);
        $this->assertTrue($collection->hasAppointment($entity));
        $this->assertFalse($collection->hasAppointment($entity2));
    }

    public function testHasTime()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasTime());
        $entity->date = 0;
        $this->assertFalse($entity->hasTime());
    }
}
