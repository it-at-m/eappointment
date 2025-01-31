<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Availability;
use \BO\Zmsentities\Collection\AvailabilityList;

/**
 * @SuppressWarnings(PublicMethod)
 *
 */
class AvailabilityTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-01-01 12:50:00'; //friday

    public $entityclass = '\BO\Zmsentities\Availability';

    public $collectionclass = '\BO\Zmsentities\Collection\AvailabilityList';

    protected static $weekdayNameList = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday'
    ];

    public function testHasDay()
    {
        $entity = new $this->entityclass();
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")
            ->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")
            ->format('H:i');
        $entity['weekday']['friday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);

        $this->assertTrue($entity->getDuration() == 60, "Availability duration does not match");
        $this->assertFalse(
            $entity->hasDate($time, $now),
            'Availability should not be valid on startDate, case bookable on 1 day in the future'
        );
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability should be valid in the second week afterwards'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+3days'), $now),
            'Availability should not be valid on a monday if only friday is given'
        );
        $entity['weekday']['monday'] = 1;
        $this->assertTrue(
            $entity->hasDate($time->modify('+10days'), $now),
            'Availability should be valid on a monday if friday and monday is given'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+1week'), $now),
            'Availability should not be valid in the first week afterwards if repeating is set to every 2 weeks'
        );
        $entity['repeat']['weekOfMonth'] = 2;
        $this->assertEquals(
            $entity['repeat']['weekOfMonth'],
            \BO\Zmsentities\Helper\DateTime::create($time->modify('+1week'))->getWeekOfMonth()
        );

        $this->assertTrue(
            $entity->hasDate($time->modify('+1week'), $now),
            'Availability should be valid in the second week of the month'
        );


        $this->assertFalse(
            $entity->hasDate($time->modify('+3week'), $now),
            'Availability should be not valid in the fourth week of the month'
        );

        $entity['startDate'] = $time->modify('+1day')
            ->getTimestamp();
        $entity['repeat']['afterWeeks'] = 2;
        $entity['repeat']['weekOfMonth'] = 0;
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability on afterWeeks=2 should be valid in the third week after startDate +1 day'
        );
        $entity['endDate'] = $time->modify("-1day")
            ->getTimestamp();
        $this->assertFalse($entity->hasDate($time->modify('+1week'), $now), 'EndDate is smaller than startDate');
    }

    public function testWeekOnDifferentBeginnings()
    {
        $entity = new $this->entityclass();
        // A friday
        $time = new \DateTimeImmutable('2016-04-01 11:55:00');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")
            ->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")
            ->format('H:i');
        $entity['weekday']['friday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);

        // start = friday
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability should be valid in the second week afterwards, if beginning is in the same week'
        );
        // start = saturday
        $entity['startDate'] = $time->modify('+1day')->getTimestamp();
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability should be valid in the second week afterwards, if beginning is in the same week'
        );
        // start = sunday
        $entity['startDate'] = $time->modify('+2day')->getTimestamp();
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability should be valid in the second week afterwards, if beginning is in the same week'
        );
        // start = monday
        $entity['startDate'] = $time->modify('+3day')->getTimestamp();
        $this->assertFalse(
            $entity->hasDate($time->modify('+2week'), $now),
            'Availability should not be valid in the second week afterwards, if beginning is in the next week'
        );
    }

    public function testWeekCase35851()
    {
        $entity = new $this->entityclass();
        // A friday
        $time = new \DateTimeImmutable('2019-01-02 11:55:00');
        $now = new \DateTimeImmutable('2019-02-28 11:55:00');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+12month")
            ->getTimestamp();
        $entity['endTime'] = $time->modify("+12month 17:10:00")
            ->format('H:i');
        $entity['weekday']['wednesday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);

        $this->assertTrue(
            $entity->hasDate(new \DateTimeImmutable('2019-03-13 00:00:00'), $now),
            'This week 13.3. should be valid'
        );
        $this->assertFalse(
            $entity->hasDate(new \DateTimeImmutable('2019-03-20 00:00:00'), $now),
            'This week 20.3. should not be valid'
        );
        $this->assertTrue(
            $entity->hasDate(new \DateTimeImmutable('2019-03-27 00:00:00'), $now),
            'This week 27.3. should be valid'
        );
        $this->assertFalse(
            $entity->hasDate(new \DateTimeImmutable('2019-04-03 00:00:00'), $now),
            'This week 3.4. should not be valid'
        );
    }

    public function testGetAvailableSecondsPerDay()
    {
        $entity = (new $this->entityclass())->getExample();
        $withCalculatedSlots = $entity->withCalculatedSlots();
        $this->assertEquals(6534000, $withCalculatedSlots->getAvailableSecondsPerDay());
    }

    public function testGetAvailableSecondsOnDateTime()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();
        $entity['startDate'] = $time->getTimestamp();
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $entity['weekday']['friday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);
        $collection = $collection->withCalculatedSlots();
        $this->assertEquals(6534000, $collection->getAvailableSecondsOnDateTime($time));
    }

    public function testDayOff()
    {
        $dayOffTime = new \DateTimeImmutable(self::DEFAULT_TIME);
        $time = new \DateTimeImmutable('2015-11-26 12:30:00');
        $now = new \DateTimeImmutable('2015-11-26 12:30:00');
        $entity = new $this->entityclass();
        $entity['startDate'] = $time->getTimestamp();
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $dayOff[] = new \BO\Zmsentities\Dayoff(array(
            'date' => 1451649000,
            'name' => 'Neujahr'
        ));
        $entity['scope'] = (new \BO\Zmsentities\Scope(
            array('dayoff' => $dayOff)
        ));
        $this->assertTrue(
            $entity->hasDayOff($dayOffTime),
            'Time ' . $time->format('Y-m-d') . ' must be dayoff time'
        );
        $this->assertFalse(
            $entity->hasDayOff($time),
            'Time ' . $time->format('Y-m-d') . ' is not a dayoff time'
        );

        $weekDayName = self::$weekdayNameList[$dayOffTime->format('w')];
        $entity['weekday'][$weekDayName] = 1;
        $this->assertFalse(
            $entity->hasDate($dayOffTime, $now),
            'Time ' . $dayOffTime->format('Y-m-d') . ' is a dayoff time'
        );
    }

    public function testDayOffFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\DayoffMissing');
        $dayOffTime = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->hasDayOff($dayOffTime);
    }

    public function testIsNewerThan()
    {
        $dateTime = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = new $this->entityclass();
        $this->assertFalse($entity->isNewerThan($dateTime));
        $entity->lastChange = $dateTime->modify('+1 day')->getTimestamp();
        $this->assertTrue($entity->isNewerThan($dateTime));
    }

    public function testIsBookable()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = new $this->entityclass();
        $entity['startDate'] = $time->getTimestamp();
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $endInDays = $entity->bookable['endInDays'];

        $this->assertFalse(
            $entity->isBookable($time, $time),
            'Availability default should be, that you cannot reserve an appointment for today'
        );
        $this->assertTrue(
            $entity->isBookable($time->modify('+1day'), $time),
            'Availability default should be, that you cannot reserve an appointment for the next day'
        );

        try {
            $entity->bookable['endInDays'] = null;
            $entity->isBookable($time->modify('+7 days'), $time);
            $this->fail("Expected exception ProcessBookableFailed not thrown");
        } catch (\BO\Zmsentities\Exception\ProcessBookableFailed $exception) {
            $this->assertEquals(
                'Undefined end time for booking, try to set the scope properly',
                $exception->getMessage()
            );
        }
        $entity->bookable['endInDays'] = $endInDays;
        try {
            $entity->bookable['startInDays'] = null;
            $entity->isBookable($time->modify('+7 days'), $time);
            $this->fail("Expected exception ProcessBookableFailed not thrown");
        } catch (\BO\Zmsentities\Exception\ProcessBookableFailed $exception) {
            $this->assertEquals(
                'Undefined start time for booking, try to set the scope properly',
                $exception->getMessage()
            );
        }
        $entity->bookable['endInDays'] = null;

        $entity['scope'] = (new \BO\Zmsentities\Scope())->getExample();
        $this->assertFalse(
            $entity->isBookable($time, $time),
            'Availability default should be, that you cannot reserve an appointment for today'
        );

        $entity->bookable['startInDays'] = 2;
        $entity->bookable['endInDays'] = 1;
        $this->assertFalse(
            $entity->isBookable($time->modify("+1month"), $time),
            'Availability endInDays is before startInDays'
        );

        $entity['endDate'] = $time->modify("+90 day")->getTimestamp();
        $entity->bookable['endInDays'] = 60;
        $entity['workstationCount']['intern'] = 3;
        $entity['weekday']['friday'] = 1;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);
        $this->assertTrue($entity->hasBookableDates($time), 'Availability shoud have bookable dates');
        $entity['workstationCount']['intern'] = 0;
        $this->assertFalse(
            $entity->hasBookableDates($time),
            'Availability should not be bookable on missing workstation'
        );
        $entity['workstationCount']['intern'] = 1;
        $entity['weekday']['friday'] = 0;
        $this->assertFalse(
            $entity->hasBookableDates($time),
            'Availability should not be bookable on missing weekday'
        );
        $entity['weekday']['friday'] = 1;
        $entity['startDate'] = $time->modify("+61 day")->getTimestamp();
        $this->assertFalse(
            $entity->hasBookableDates($time),
            'Availability should not be bookable if not started'
        );
        $entity['startDate'] = $time->modify("-60 day")->getTimestamp();
        $entity['endDate'] = $time->modify("-2 day")->getTimestamp();
        $this->assertFalse(
            $entity->hasBookableDates($time),
            'Availability should not be bookable if in the past'
        );
        $entity['endDate'] = $time->modify("+20 day")->getTimestamp();
        $this->assertFalse(
            $entity->hasDateBetween($time->modify("-2 day"), $time->modify("-1 day"), $time),
            'Availability should not have dates in the past'
        );
    }

    public function testIsBookableByScopeEnd()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = new $this->entityclass();
        $entity->bookable['startInDays'] = null;
        $entity->bookable['endInDays'] = null;
        $entity['startDate'] = $time->modify("-60 day")->getTimestamp();
        $entity['endDate'] = $time->modify("+200 day")->getTimestamp();
        $entity['scope'] = (new \BO\Zmsentities\Scope())->getExample();
        //error_log(__METHOD__ . ": $entity ". $time->format('c'));
        $this->assertTrue(
            $entity->isBookable($time->modify("+1month"), $time),
            'Availability endInDays is before startInDays'
        );
    }

    public function testIsBookableMidnight()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = new $this->entityclass();
        $entity->bookable['startInDays'] = 0;
        $entity['startDate'] = $time->getTimestamp();
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $entity['startTime'] = $time->modify('-1 hour')->format('H:i');
        $this->assertTrue(
            $entity->isBookable($time->modify("+2month"), $time),
            "Last Day should be bookable"
        );
        $entity['startTime'] = $time->modify('+1 hour')->format('H:i');
        $this->assertFalse(
            $entity->isBookable($time->modify("+2month"), $time),
            "Last Day opening hour did not started yet"
        );
        $entity['endDate'] = $entity['startDate'];
        $entity->bookable['endInDays'] = 0;
        $this->assertTrue(
            $entity->isBookable($time, $time),
            "Current Day should be bookable independent of current time"
        );
    }

    public function testIsBookableOnEndDate()
    {
        $time = new \DateTimeImmutable('2016-02-01 11:00');
        $entityOH = $this->getExampleWithTypeOpeningHours($time);
        $entityOH->bookable['startInDays'] = 0;
        $entityOH->bookable['endInDays'] = 29;
        $this->assertFalse(
            $entityOH->isBookable($time->modify("+1month"), $time),
            'Availability endInDays is before startInDays'
        );
    }

    public function testSlotList()
    {
        $slotListResult = new \BO\Zmsentities\Collection\SlotList(
            array(
                new \BO\Zmsentities\Slot(
                    array(
                        'time' => '12:00',
                        'public' => 0,
                        'callcenter' => 0,
                        'intern' => 3
                    )
                ),
                new \BO\Zmsentities\Slot(
                    array(
                        'time' => '13:30',
                        'public' => 0,
                        'callcenter' => 0,
                        'intern' => 3
                    )
                ),
                new \BO\Zmsentities\Slot(
                    array(
                        'time' => '15:00',
                        'public' => 0,
                        'callcenter' => 0,
                        'intern' => 3
                    )
                ),
                new \BO\Zmsentities\Slot(
                    array(
                        'time' => '16:30',
                        'public' => 0,
                        'callcenter' => 0,
                        'intern' => 3
                    )
                )
            )
            // If the last slot is equal to the stop time, there should not be a slot! (Do not remove this comment)
            // 4 => array (
            // 'time' => '18:00',
            // 'public' => 0,
            // 'callcenter' => 0,
            // 'intern' => 3,
            // ),
        );
        $time = new \DateTimeImmutable('12:00:00');
        $entity = new $this->entityclass(
            [
                'startTime' => $time->format('H:i'),
                'endTime' => $time->modify("18:00:00")
                    ->format('H:i'),
                'slotTimeInMinutes' => 90
            ]
        );
        $entity['workstationCount']['intern'] = 3;
        $slotList = $entity->getSlotList();
        $this->assertEquals($slotList, $slotListResult);
        $entity['slotTimeInMinutes'] = 0;
        $slotList = $entity->getSlotList();
        $this->assertEquals($slotList, new \BO\Zmsentities\Collection\SlotList());
        // var_dump((string)$entity);
    }

    public function testSlotListRealExample()
    {
        $entity = new $this->entityclass(
            [
                'id' => '93181',
                'weekday' => array(
                    'monday' => '0',
                    'tuesday' => '4',
                    'wednesday' => '0',
                    'thursday' => '0',
                    'friday' => '0',
                    'saturday' => '0',
                    'sunday' => '0'
                ),
                'repeat' => array(
                    'afterWeeks' => '2',
                    'weekOfMonth' => '0'
                ),
                'bookable' => array(
                    'startInDays' => '0',
                    'endInDays' => '60'
                ),
                'workstationCount' => array(
                    'public' => '2',
                    'callcenter' => '2',
                    'intern' => '2'
                ),
                'slotTimeInMinutes' => '15',
                'startDate' => '1461024000',
                'endDate' => '1461024000',
                'startTime' => '12:00:00',
                'endTime' => '16:00:00',
                'multipleSlotsAllowed' => '0'
            ]
        );
        $slotList = $entity->getSlotList();
        $this->assertTrue(16 == count($slotList));
        //$this->assertTrue($entity->hasDate(new \DateTime('2016-04-19')));
    }

    public function testWithCalculatedSlots()
    {
        $entity = (new $this->entityclass())->getExample();
        $withCalculatedSlots = $entity->withCalculatedSlots();
        $this->assertTrue(99 == $withCalculatedSlots['workstationCount']['public'], $withCalculatedSlots);
    }

    public function testGetSlotList()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $slotList = $collection->getSlotList();
        $this->assertTrue(33 == count($slotList));
        $this->assertEquals('10:00', $slotList->getFirst()['time']);
        $this->assertEquals('10:10', $slotList[1]['time']);
    }

    public function testToString()
    {
        $entity = (new $this->entityclass())->getExample();
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity['startDate'] = $time->getTimestamp();
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $entity['repeat']['afterWeeks'] = 1;
        $entity['repeat']['weekOfMonth'] = 1;
        $this->assertStringContainsString('Availability.appointment #1234', $entity->__toString());
    }

    public function testCollection()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);

        $entityOH = $this->getExampleWithTypeOpeningHours($time);
        $collection->addEntity($entityOH);

        $this->assertTrue($collection->isOpened($time));
        $this->assertTrue($collection->isOpenedByDate($time));
        $this->assertFalse($collection->isOpenedByDate($time->modify('+ 1 day')));

        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            2 == count($collection),
            'Amount of entities in collection failed, 2 expected (' .
            count($collection) . ' found)'
        );

        $this->assertTrue(
            10 == $collection->getMaxWorkstationCount(),
            'Failed to get correct max workstation count, 10 expected'
        );

        $this->assertTrue(
            99 == $collection->withCalculatedSlots()[0]['workstationCount']['public'],
            'Failed to get list with calculated slots'
        );
        $collection->addEntity($entity);
        $this->assertTrue($collection->count() == 3);
        $collection = $collection->withOutDoubles();
        $this->assertTrue($collection->count() == 2);

        $this->assertEquals(1, $collection->withDateTime($time)->count());
        $this->assertEquals(1, $collection->withType('openinghours')->count());
        $this->assertEquals(1, $collection->withType('appointment')->count());

        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $this->assertFalse($collection->hasAppointment($appointment));
        $appointment->setTime(self::DEFAULT_TIME);
        $this->assertTrue($collection->hasAppointment($appointment));
    }

    public function testUnopenedNoDay()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new $this->collectionclass();
        $entityOH = $this->getExampleWithTypeOpeningHours($time);
        $entityOH->endTime = '11:00:00';
        $collection->addEntity($entityOH);
        $this->assertFalse($collection->isOpened($time));
        $this->assertTrue($collection->isOpenedByDate($time), "Day should be covered although time is after endTime");
    }

    public function testUnopenedWrongType()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entityOH = $this->getExampleWithTypeOpeningHours($time);
        $entityOH->endTime = '11:00:00';
        $this->assertFalse($entityOH->isOpened($time, 'appointment'));
    }

    public function testOneTimer()
    {
        $time = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entityOH = $this->getExampleWithTypeOpeningHours($time);
        $entityOH->offsetSet('endTime', '13:00:00');
        $entityOH->offsetSet('repeat', array('afterWeeks' => 0, 'weekOfMonth' => 0));
        $this->assertTrue($entityOH->isOpened($time));
    }

    // only testable with correct week of month calcuation in Helper\Datetime Class
    /*
    public function testX1Week()
    {
        $entity = new $this->entityclass();
        $time = new \DateTimeImmutable('2016-04-01 11:55:00');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")
            ->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")
            ->format('H:i');
        $entity['weekday']['wednesday'] = 1;
        $entity['repeat']['afterWeeks'] = 0;
        $entity['repeat']['weekOfMonth'] = 1;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);

        $this->assertTrue(
            $entity->hasWeek($time),
            'This day, Friday 1.4.2016, is in first week of month and should be valid'
        );
        $this->assertFalse(
            $entity->hasWeek(new \DateTimeImmutable('2016-04-04 11:55:00')),
            'This day, Monday 4.4.2016, is in second week of month and should be unvalid'
        );
        $this->assertFalse(
            $entity->hasWeek(new \DateTimeImmutable('2016-05-02 11:55:00')),
            'This day, Monday 2.5.2016, is in second week of month and should be unvalid'
        );
        $this->assertFalse(
            $entity->hasWeek(new \DateTimeImmutable('2016-05-09 11:55:00')),
            'This day, Monday 9.5.2016, is in second week of month and should be unvalid'
        );
        $this->assertFalse(
            $entity->hasWeekDay($time),
            'This day, Friday 1.4.2016, is not booked weekday'
        );
        $this->assertTrue(
            $entity->hasWeekDay(new \DateTimeImmutable('2016-05-04 11:55:00')),
            'This day, Wednesday 4.5.2016, is a valid booked weekday'
        );
    }

    public function testX2Week()
    {
        $entity = new $this->entityclass();
        $time = new \DateTimeImmutable('2016-04-01 11:55:00');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")
            ->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")
            ->format('H:i');
        $entity['weekday']['wednesday'] = 1;
        $entity['repeat']['afterWeeks'] = 0;
        $entity['repeat']['weekOfMonth'] = 2;
        $entity['scope'] = new \BO\Zmsentities\Scope([
            'dayoff' => new \BO\Zmsentities\Collection\DayoffList(),
        ]);

        $this->assertTrue(
            $entity->hasWeek(new \DateTimeImmutable('2016-04-04 11:55:00')),
            'This day, Monday 4.4.2016, is in second week of month and should be valid'
        );
        $this->assertFalse(
            $entity->hasWeek($time),
            'This day, Friday 1.4.2016, is in first week of month and should be unvalid'
        );
        $this->assertTrue(
            $entity->hasWeek(new \DateTimeImmutable('2016-05-02 11:55:00')),
            'This day, Monday 2.5.2016, is in second week of month and should be valid'
        );
        $this->assertFalse(
            $entity->hasWeek(new \DateTimeImmutable('2016-05-09 11:55:00')),
            'This day, Monday 9.5.2016, is in third week of month and should be unvalid'
        );
        $this->assertFalse(
            $entity->hasWeekDay($time),
            'This day, Monday 4.4.2016, is not booked weekday'
        );
        $this->assertTrue(
            $entity->hasWeekDay(new \DateTimeImmutable('2016-05-11 11:55:00')),
            'This day, Wednesday 11.5.2016, is a valid booked weekday'
        );
    }
    */

    public function testWithScope()
    {
        $entity = (new $this->entityclass())->getExample();
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $entity = $entity->withScope($scope);
        $this->assertEquals(123, $entity->scope->id);

        $collection = new $this->collectionclass();
        $entity2 = (new $this->entityclass())->getExample();
        $collection->addEntity($entity2);
        $collection = $collection->withScope($scope);
        $this->assertEquals(123, $collection->getFirst()->scope->id);
    }

    /**
     * @SuppressWarnings(Length)
     *
     */
    public function testConflicts()
    {
        $availability = new Availability([
            'id' => '1',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '12:00:00',
            'endTime' => '16:00:00',
        ]);
        $availabilityOverlap = new Availability([
            'id' => '2',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '10:00:00',
            'endTime' => '13:00:00',
        ]);
        $availabilitySlotsize = new Availability([
            'id' => '3',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '25',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '09:00:00',
            'endTime' => '10:00:00',
        ]);
        $availabilityOverlap2 = new Availability([
            'id' => '4',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '15:00:00',
            'endTime' => '17:00:00',
        ]);
        $availabilityEqual = new Availability([
            'id' => '5',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '12:00:00',
            'endTime' => '16:00:00',
        ]);
        $availabilityWrongStartAndEnd = new Availability([
            'id' => '6',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-20'),
            'startTime' => '17:00:00',
            'endTime' => '11:00:00',
        ]);
        $availabilityList = new AvailabilityList([
            $availability,
            $availabilityOverlap,
            $availabilitySlotsize,
            $availabilityOverlap2,
            $availabilityEqual,
            $availabilityWrongStartAndEnd
        ]);
        $startDate = new \DateTimeImmutable('2016-04-19 09:00');
        $endDate = new \DateTimeImmutable('2016-04-19 16:00');
        $conflicts = $availabilityList->checkAllVsExistingConflicts($startDate, $startDate);
        $list = [];
        foreach ($conflicts as $conflict) {
            /*error_log(
                "\n$conflict " .
                $conflict->amendment .
                "(ID: ". $conflict->getFirstAppointment()->getAvailability()->getId() ." ". $conflict->getFirstAppointment()->getAvailability()->getStartDateTime() ." - ". $conflict->getFirstAppointment()->getAvailability()->getEndDateTime() .")"
            );
            */

            $id = $conflict->getFirstAppointment()->getAvailability()->getId();
            if (!isset($list[$conflict->amendment])) {
                $list[$conflict->amendment] = [];
            }
            if (!isset($list[$conflict->amendment][$id])) {
                $list[$conflict->amendment][$id] = 1;
            } else {
                $list[$conflict->amendment][$id] += 1;
            }
        }

        // Assertion for overlapping availabilities - Availability 1 and 5
        $this->assertEquals(
            1,
            $list[
                "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 10:00 - 13:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]"
            ][1]
        );

        $this->assertEquals(
            1,
            $list[
                "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 10:00 - 13:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]"
            ][5]
        );

        // Assertions for overlapping availabilities - Availability 2, 4
        $this->assertEquals(
            2,
            $list[
                "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 10:00 - 13:00]"
            ][2]
        );

        $this->assertEquals(
            2,
            $list[
                "Konflikt: Zwei Öffnungszeiten überschneiden sich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 15:00 - 17:00]"
            ][4]
        );

        // Assertions for exact matches - Availability 1 and 5
        $this->assertEquals(
            1,
            $list[
                "Konflikt: Zwei Öffnungszeiten sind gleich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]"
            ][1]
        );

        $this->assertEquals(
            1,
            $list[
                "Konflikt: Zwei Öffnungszeiten sind gleich.\n" .
                "Bestehende Öffnungszeit:&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]\n" .
                "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[19.04.2016 - 19.04.2016, 12:00 - 16:00]"
            ][5]
        );

        // Assertion for slot size conflict
        $this->assertEquals(
            1,
            $list[
                'Der eingestellte Zeitschlitz von 25 Minuten sollte in die eingestellte Uhrzeit passen.'
            ][3]
        );


    }

    protected function getExampleWithTypeOpeningHours(\DateTimeImmutable $time)
    {
        return new $this->entityclass(
            [
                'id' => '93181',
                'weekday' => array(
                    'monday' => '0',
                    'tuesday' => '0',
                    'wednesday' => '0',
                    'thursday' => '0',
                    'friday' => '1',
                    'saturday' => '0',
                    'sunday' => '0'
                ),
                'repeat' => array(
                    'afterWeeks' => '2',
                    'weekOfMonth' => '0'
                ),
                'bookable' => array(
                    'startInDays' => '0',
                    'endInDays' => '60'
                ),
                'workstationCount' => array(
                    'public' => '2',
                    'callcenter' => '2',
                    'intern' => '2'
                ),
                'slotTimeInMinutes' => '15',
                'startDate' => $time->getTimestamp(),
                'endDate' => $time->getTimestamp(),
                'startTime' => '12:00:00',
                'endTime' => '16:00:00',
                'multipleSlotsAllowed' => '0',
                'scope' => array('id' => 141),
                'type' => 'openinghours'
            ]
        );
    }

    public function testValidateStartTime()
    {
        $entity = new Availability();
        $today = new \DateTimeImmutable('2024-01-15 12:00:00');
        $tomorrow = new \DateTimeImmutable('2024-01-16 12:00:00');
        $selectedDate = new \DateTimeImmutable('2024-01-16 12:00:00');
        
        $startDate = new \DateTimeImmutable('2024-01-17 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-17 16:00:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(1, $errors);
        $this->assertEquals('startTimeFuture', $errors[0]['type']);
        
        $startDate = new \DateTimeImmutable('2024-01-15 23:30:00');
        $endDate = new \DateTimeImmutable('2024-01-16 00:30:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(1, $errors);
        $this->assertEquals('startOfDay', $errors[0]['type']);
        
        $startDate = new \DateTimeImmutable('2024-01-15 10:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 16:00:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(0, $errors);
    }
    
    public function testValidateEndTime()
    {
        $entity = new Availability();
        
        $startDate = new \DateTimeImmutable('2024-01-15 14:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $errors = $entity->validateEndTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('endTime', $errors[0]['type']);
        
        $startDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-14 14:00:00');
        $errors = $entity->validateEndTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('endTime', $errors[0]['type']);
        
        $startDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 16:00:00');
        $errors = $entity->validateEndTime($startDate, $endDate);
        $this->assertCount(0, $errors);
    }
    
    public function testValidateOriginEndTimeWithPastAndFuture()
    {
        $entity = new Availability();
        $today = new \DateTimeImmutable('2024-01-15 12:00:00');
        $yesterday = new \DateTimeImmutable('2024-01-14 12:00:00');
        $selectedDate = new \DateTimeImmutable('2024-01-16 12:00:00');
        
        $startDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-14 10:00:00');
        $errors = $entity->validateOriginEndTime($today, $yesterday, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(2, $errors);
        $this->assertEquals('endTimeFuture', $errors[0]['type']);
        $this->assertEquals('endTimePast', $errors[1]['type']);
        
        $errors = $entity->validateOriginEndTime($today, $yesterday, $startDate, $endDate, $selectedDate, 'origin');
        $this->assertCount(0, $errors);
        
        $endDate = new \DateTimeImmutable('2024-01-16 16:00:00');
        $errors = $entity->validateOriginEndTime($today, $yesterday, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(0, $errors);
    }
    
    public function testValidateType()
    {
        $entity = new Availability();
        
        $errors = $entity->validateType('');
        $this->assertCount(1, $errors);
        $this->assertEquals('type', $errors[0]['type']);
        
        $errors = $entity->validateType('appointment');
        $this->assertCount(0, $errors);
    }
    
    public function testValidateSlotTime()
    {
        $entity = new Availability();
        $startDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 14:00:00');
        
        $entity['slotTimeInMinutes'] = 0;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('slotTime', $errors[0]['type']);
        
        $entity['slotTimeInMinutes'] = 25;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('slotCount', $errors[0]['type']);
        
        $entity['slotTimeInMinutes'] = 30;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(0, $errors);
    }
    
    public function testValidateBookableDayRange()
    {
        $entity = new Availability();
        
        $errors = $entity->validateBookableDayRange(10, 5);
        $this->assertCount(1, $errors);
        $this->assertEquals('bookableDayRange', $errors[0]['type']);
        
        $errors = $entity->validateBookableDayRange(5, 10);
        $this->assertCount(0, $errors);
    }

    public function testValidateStartTimeMaintenanceWindow()
    {
        // Test maintenance window validation (23:00-01:00)
        $entity = new Availability([
            'scope' => ['id' => 141],
            'type' => 'appointment',
            'weekday' => ['monday' => true],
            'startDate' => strtotime('2024-01-15'),
            'endDate' => strtotime('2024-01-16'),
            'startTime' => '23:00',  // Entity's start time in maintenance window
            'endTime' => '01:00'     // Entity's end time in maintenance window
        ]);
        
        $today = new \DateTimeImmutable('2024-01-15 12:00:00');
        $tomorrow = new \DateTimeImmutable('2024-01-16 12:00:00');
        $selectedDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        
        $startDate = new \DateTimeImmutable('2024-01-15 23:00:00');
        $endDate = new \DateTimeImmutable('2024-01-16 01:00:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(1, $errors);
        $this->assertEquals('startOfDay', $errors[0]['type']);
        
        $entity['startTime'] = '22:00';
        $entity['endTime'] = '00:30';
        $startDate = new \DateTimeImmutable('2024-01-15 22:00:00');
        $endDate = new \DateTimeImmutable('2024-01-16 00:30:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(1, $errors);
        $this->assertEquals('startOfDay', $errors[0]['type']);
        
        $entity['startTime'] = '10:00';
        $entity['endTime'] = '16:00';
        $startDate = new \DateTimeImmutable('2024-01-15 10:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 16:00:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(0, $errors);
    }
    
    public function testValidateStartTimeFutureDate()
    {
        $entity = new Availability([
            'scope' => ['id' => 141],
            'type' => 'appointment',
            'weekday' => ['monday' => true],
            'startDate' => strtotime('2024-01-15'),
            'endDate' => strtotime('2024-01-16'),
            'startTime' => '10:00',
            'endTime' => '16:00'
        ]);
        
        $today = new \DateTimeImmutable('2024-01-15 12:00:00');
        $tomorrow = new \DateTimeImmutable('2024-01-16 12:00:00');
        $selectedDate = new \DateTimeImmutable('2024-01-16 12:00:00'); // Set to tomorrow

        $startDate = new \DateTimeImmutable('2024-01-17 10:00:00'); // Day after tomorrow
        $endDate = new \DateTimeImmutable('2024-01-17 16:00:00');
        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'current');
        $this->assertCount(1, $errors);
        $this->assertEquals('startTimeFuture', $errors[0]['type']);

        $errors = $entity->validateStartTime($today, $tomorrow, $startDate, $endDate, $selectedDate, 'future');
        $this->assertCount(0, $errors);
    }
    
    public function testValidateEndTimeMinutePrecision()
    {
        $entity = new Availability();

        $startDate = new \DateTimeImmutable('2024-01-15 14:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 14:00:00');
        $errors = $entity->validateEndTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('endTime', $errors[0]['type']);

        $startDate = new \DateTimeImmutable('2024-01-15 14:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 14:01:00');
        $errors = $entity->validateEndTime($startDate, $endDate);
        $this->assertCount(0, $errors);
    }
    
    public function testValidateSlotTimeDivisibility()
    {
        $entity = new Availability();
        $startDate = new \DateTimeImmutable('2024-01-15 12:00:00');
        $endDate = new \DateTimeImmutable('2024-01-15 13:00:00');

        $entity['slotTimeInMinutes'] = 0;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('slotTime', $errors[0]['type']);

        $entity['slotTimeInMinutes'] = 25;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(1, $errors);
        $this->assertEquals('slotCount', $errors[0]['type']);

        $entity['slotTimeInMinutes'] = 15;
        $errors = $entity->validateSlotTime($startDate, $endDate);
        $this->assertCount(0, $errors);
    }
    
    public function testValidateBookableDayRangeOrder()
    {
        $entity = new Availability();

        $errors = $entity->validateBookableDayRange(10, 5);
        $this->assertCount(1, $errors);
        $this->assertEquals('bookableDayRange', $errors[0]['type']);

        $errors = $entity->validateBookableDayRange(5, 5);
        $this->assertCount(0, $errors);

        $errors = $entity->validateBookableDayRange(-5, -2);
        $this->assertCount(0, $errors);
    }
    
    public function testValidateTypeAllowedValues()
    {
        $entity = new Availability();

        $errors = $entity->validateType('');
        $this->assertCount(1, $errors);
        $this->assertEquals('type', $errors[0]['type']);

        $validTypes = ['appointment', 'openinghours', 'intern', 'callcenter'];
        foreach ($validTypes as $type) {
            $errors = $entity->validateType($type);
            $this->assertCount(0, $errors, "Type '$type' should be valid");
        }
    }
}
