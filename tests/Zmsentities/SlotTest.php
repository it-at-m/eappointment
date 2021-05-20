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
        $this->assertEquals('slot#sum@10:00 p/c/i=3/8/10', $entity->__toString(), 'SlotTime String does not match');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = new $this->entityclass(array(
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

        $this->assertStringContainsString('slotlist#', (string)$collection);
    }

    public function testReducedSlots()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = $this->getExample();
        $entity3 = $this->getExample();
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $collection->addEntity($entity3);
        $collection0 = $collection->withReducedSlots(0);
        $this->assertEquals(3, $collection0[0]['public']);
        $this->assertEquals(3, $collection0[1]['public']);
        $this->assertEquals(3, $collection0[2]['public']);
        $collection1 = $collection->withReducedSlots(1);
        $this->assertEquals(3, $collection1[0]['public']);
        $this->assertEquals(3, $collection1[1]['public']);
        $this->assertEquals(3, $collection1[2]['public']);
        $collection2 = $collection->withReducedSlots(2);
        $this->assertEquals(3, $collection2[0]['public']);
        $this->assertEquals(3, $collection2[1]['public']);
        $this->assertEquals(0, $collection2[2]['public']);
        $collection3 = $collection->withReducedSlots(3);
        $this->assertEquals(3, $collection3[0]['public']);
        $this->assertEquals(0, $collection3[1]['public']);
        $this->assertEquals(0, $collection3[2]['public']);
        $collection4 = $collection->withReducedSlots(4);
        $this->assertEquals(0, $collection4[0]['public']);
        $this->assertEquals(0, $collection4[1]['public']);
        $this->assertEquals(0, $collection4[2]['public']);
    }

    public function testSlotByAppointment()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $appointment->setTime('12:50');
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = new $this->entityclass(array(
            'public' => 2,
            'intern' => 9,
            'callcenter' => 5,
            'time' => '12:50'
        ));
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $this->assertEquals('12:50', $collection->getByDateTime($now)['time']);
        $this->assertFalse($collection->getByDateTime($now->setTime('10', '00')));

        $entity3 = new $this->entityclass(array(
            'public' => 2,
            'intern' => 9,
            'callcenter' => 5,
            'time' => '13:00'
        ));
        $collection->addEntity($entity3);
        $slotList = $collection->withSlotsForAppointment($appointment);
        $this->assertTrue(2 == count($slotList));
        $this->assertEquals('12:50', $slotList->getFirst()['time']);
    }

    public function testSlotByAppointmentFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\AppointmentNotFitInSlotList');
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $appointment->setTime('12:50');
        $appointment->slotCount = 3;
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = new $this->entityclass(array(
            'public' => 2,
            'intern' => 9,
            'callcenter' => 5,
            'time' => '12:50'
        ));
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $slotList = $collection->withSlotsForAppointment($appointment);
    }

    public function testAvailableForAll()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = new $this->entityclass(array(
            'public' => 0,
            'intern' => 9,
            'callcenter' => 5,
            'time' => '12:50'
        ));
        $collection->addEntity($entity);
        $this->assertTrue($collection->isAvailableForAll('public'));

        $collection->addEntity($entity2);
        $this->assertFalse($collection->isAvailableForAll('public'));
    }

    public function testGetFreeProcesses()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $freeProcesses = $collection->getFreeProcesses('2016-04-01', $scope, $availability, 'public', [], 0);
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

    public function testSlotFullException()
    {
        $this->expectException('\BO\Zmsentities\Exception\SlotFull');
        $entity = $this->getExample();
        $entity->intern = 0;
        $entity->removeAppointment();
    }

    public function testGetFreeProcessesFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\SlotRequiredWithoutReducing');
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $collection->getFreeProcesses('2016-04-01', $scope, $availability, 'public', '123456', 2);
    }

    public function testGetFreeProcessesException()
    {
        $this->expectException('\BO\Zmsentities\Exception\SlotMissingTime');
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        unset($entity['time']);
        $collection->addEntity($entity);
        $collection->getFreeProcesses(
            '2015-04-01',
            (new \BO\Zmsentities\Scope())->getExample(),
            (new \BO\Zmsentities\Availability())->getExample(),
            'public',
            '123456',
            0
        );
    }
}
