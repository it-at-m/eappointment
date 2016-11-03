<?php

namespace BO\Zmsentities\Tests;

class SlotTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-05-27 12:50:00';

    public $entityclass = '\BO\Zmsentities\Slot';

    public $collectionclass = '\BO\Zmsentities\Collection\SlotList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $time = \BO\Zmsentities\Helper\DateTime::create($availability['startTime']);
        $this->assertTrue('11:50' == $entity->getTimeString(), 'Failed to get expected slot time 11:50');
        $entity->time = $time;
        $this->assertTrue('10:00' == $entity->getTimeString(), 'Slottime should instance of DateTimeInterface');
        $entity->setTime($time);
        $this->assertTrue($entity->time == $entity->getTimeString(), 'Slottime does not match');
        $this->assertTrue($entity->hasTime(), 'Slottime 10:00 missed');
        $this->assertTrue('Slot sum@10:00 p/c/i=3/8/10' == $entity->__toString(), 'SlotTime String does not match');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = new $this->entityclass(array (
            'public' => 2,
            'intern' => 9,
            'callcenter' => 5,
            'time' => '12:50'
        ));
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(2 == count($collection), "Missing new Slot Entity in collection, 2 expected");

        $collection->takeLowerSlotValue(0, 1);
        $this->assertTrue('reduced' == $collection->getSlot(0)['type'], "Slot should be of type reduced");

        $collection->setEmptySlotValues(1);
        $this->assertTrue(0 == $collection->getSlot(1)['public'], "Slot of index 1 should have 0 slotcounts");
        $this->assertTrue(null === $collection->getSlot(2), "Slot of index 2 not expected, list reduced to index 1");

        $summerizedSlots = $collection->getSummerizedSlot();
        $this->assertTrue(2 == $summerizedSlots->public, "Summerized public slot count false, 2 expected");

        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $this->assertTrue(2 == count($collection->withTimeGreaterThan($time)));
    }

    public function testGetFreeProcesses()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $freeProcesses = $collection->getFreeProcesses('2016-04-01', $scope, $availability, 'public', '123456', 0);
        $this->assertInstanceOf(
            '\BO\Zmsentities\Appointment',
            $freeProcesses[0]->getFirstAppointment(),
            'Get Appointment failed'
            );
        $this->assertTrue(
            3 == count($freeProcesses[0]->appointments),
            'Amout of slotcount ('. count($freeProcesses[0]->appointments) .') is wrong, 3 expected'
        );
    }

    public function testGetFreeProcessesException()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        unset($entity['time']);
        $collection->addEntity($entity);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        try {
            $freeProcesses = $collection->getFreeProcesses('2015-04-01', $scope, $availability, 'public', '123456', 0);
            $this->fail("Expected exception SlotMissingTime not thrown");
        } catch (\BO\Zmsentities\Exception\SlotMissingTime $exception) {
            $this->assertContains('Time on slot not set', $exception->getMessage());
            $this->assertEquals(500, $exception->getCode());
        }

    }
}
