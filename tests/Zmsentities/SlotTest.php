<?php

namespace BO\Zmsentities\Tests;

class SlotTest extends EntityCommonTests
{

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
            'callcenter' => 5
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
    }

    public function testGetFreeProcesses()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $freeProcesses = $collection->getFreeProcesses('2016-04-01', $scope, $availability, 'public', '123456');
        $firstAppointment = $freeProcesses[0]->getFirstAppointment();
        $this->assertInstanceOf(
            '\BO\Zmsentities\Appointment',
            $firstAppointment,
            'Get Appointment failed'
            );
        $this->assertTrue(
            3 == $firstAppointment->slotCount,
            'Amout of slotcount ('. $firstAppointment->slotCount .') is wrong, 3 expected'
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
            $freeProcesses = $collection->getFreeProcesses('2015-04-01', $scope, $availability, 'public', '123456');
        } catch (\BO\Zmsentities\Exception\SlotMissingTime $exception) {
            $this->assertContains('Time on slot not set', $exception->getMessage());
            $this->assertEquals(500, $exception->getCode());
        }

    }
}
