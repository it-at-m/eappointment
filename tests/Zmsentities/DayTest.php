<?php

namespace BO\Zmsentities\Tests;

class DayTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2015-11-18 11:55:00';

    public $entityclass = '\BO\Zmsentities\Day';

    public $collectionclass = '\BO\Zmsentities\Collection\DayList';

    public function testBasic()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $this->assertContains('Day bookable@2015-11-19 with', $entity->__toString(), 'day to string failed');
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
        $collection->getDay('2016','11','18');
        $this->assertEquals( $collection->getDay('2016','11','18'), $collection['18-11-2016']);
    }
}
