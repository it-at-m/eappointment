<?php

namespace BO\Zmsentities\Tests;

class AppointmentTest extends EntityCommonTests
{

    public $entityclass = '\BO\Zmsentities\Appointment';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
    }

    public function testDate()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->availability = (new \BO\Zmsentities\Availability())->getExample();
        $time = $entity->getEndTime();
        $entity->addDate($time->getTimestamp());
        $this->assertTrue('Mittwoch 18. November 2015' == $entity->toDate(), 'German date does not match.');
        $this->assertTrue('Wednesday November 18, 2015' == $entity->toDate('en'), 'English date does not match.');
        $this->assertTrue('19:04 Uhr' == $entity->toTime(), 'German time does not match.');
        $this->assertTrue('19:04 o\'clock' == $entity->toTime('en'), 'English time does not match.');
        $entity->setDateByString('2016-05-27 11:50');
        $this->assertTrue('Freitag 27. Mai 2016' == $entity->toDate(), 'German date does not match.');
        $this->assertTrue('Friday May 27, 2016' == $entity->toDate('en'), 'English date does not match.');
        $this->assertTrue('11:50 Uhr' == $entity->toTime(), 'German time does not match.');
        $this->assertTrue('11:50 o\'clock' == $entity->toTime('en'), 'English time does not match.');
        try {
            $entity->setDateByString('2016/05/27 11:50');
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
        $this->assertTrue('3' == $entity->slotCount, 'SlotCount not added.');
    }

    public function testAvailability()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->availability = (new \BO\Zmsentities\Availability())->getExample();
        $availability = $entity->getAvailability();
        $this->assertEntity('\\BO\\Zmsentities\\Availability', $availability);
    }
}
