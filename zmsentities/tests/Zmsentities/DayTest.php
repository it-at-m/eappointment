<?php

namespace BO\Zmsentities\Tests;

class DayTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2015-11-18 11:55:00';

    public $entityclass = '\BO\Zmsentities\Day';

    public $collectionclass = '\BO\Zmsentities\Collection\DayList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertStringContainsString('Day bookable@2015-11-19 with', $entity->__toString(), 'day to string failed');
    }

    public function testCollection()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $entity2 = $this->getExample();
        $entity2->setDateTime($time);

        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $collection->addEntity($entity2);

        $this->assertEquals(19, $collection->getFirst()->day);
        $collection->setSort();
        $this->assertEquals(18, $collection->getFirst()->day);
        $this->assertTrue($collection->hasDayWithAppointments());

        //test hash cache
        $collection->getDay('2016', '11', '18');
        $this->assertEquals($collection->getDay('2016', '11', '18'), $collection['18-11-2016']);
        $this->assertEquals('2015-11-18', $collection->getFirstBookableDay()->format('Y-m-d'));
    }

    public function testGetFirstBookableDayFailed()
    {
        $entity = $this->getExample();
        $entity->status = 'notBookable';
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $this->assertEquals(null, $collection->getFirstBookableDay());
    }

    public function testWithAddedDay()
    {
        $entity = $this->getExample();
        $entity->freeAppointments = [];

        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity2 = $this->getExample();
        $entity = $entity->withAddedDay($entity2);
        $this->assertEquals(3, $entity->freeAppointments['intern']);
    }

    public function testWithAddedDayList()
    {
        $entity = $this->getExample();
        $entity->status = 'notBookable';
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);

        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity2 = $this->getExample();
        $entity2->setDateTime($time);
        $entity2->status = 'bookable';
        $collection2 = new $this->collectionclass();
        $collection2->addEntity($entity2);

        $collection->withAddedDayList($collection2);
        $this->assertEquals(2, $collection->count());
        $this->assertEquals(1, $collection->withDaysInDateRange($time, $time)->count());

        $collection = new $this->collectionclass();
        $this->assertEquals(8, $collection->withDaysFromPeriod($time, $time->modify('+7 days'))->count());
    }

    public function testGetDayHash()
    {
        $entity = $this->getExample();
        $this->assertEquals('19-11-2015', $entity->getDayHash());
    }

    public function testSetStatusByType()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $collection->setStatusByType('intern', $time);
        $this->assertEquals('bookable', $collection->getFirst()->status);
        $collection->setStatusByType('public', $time);
        $this->assertEquals('full', $collection->getFirst()->status);

        $collection->getFirst()->allAppointments['public'] = 0;
        $collection->setStatusByType('public', $time);
        $this->assertEquals('notBookable', $collection->getFirst()->status);
    }

    public function testGetWithStatus()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $entity->getWithStatus('public', $time);
        $this->assertEquals('full', $entity->status);

        $entity->getWithStatus('public', $time->modify('+ 3 day'));
        $this->assertEquals('restricted', $entity->status);
    }
}
