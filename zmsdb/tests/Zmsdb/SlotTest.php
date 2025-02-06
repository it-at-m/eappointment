<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Slot;
use \BO\Zmsentities\Collection\SlotList as Collection;

class SlotTest extends Base
{
    const TEST_AVAILABILITY_ID = 68985;

    public function testWriteOptimizedSlotTables()
    {
        //Attention, rollback does not work here!
        $status = (new Slot())->writeOptimizedSlotTables();
        $this->assertEquals(true, $status, "Optimization for tables should return true");
    }

    public function testChanged()
    {
        $changed = (new Slot())->readLastChangedTime();
        //var_dump($changed->format('c'));
        $this->assertTrue($changed instanceof \DateTimeInterface);
    }

    public function testNoChange()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots without change");
    }

    public function testChangedAvailability()
    {
        $availability = $this->readTestAvailability();
        $availability->workstationCount['intern'] = 5;
        $availability = (new \BO\Zmsdb\Availability())->updateEntity(static::TEST_AVAILABILITY_ID, $availability, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on change");
    }

    public function testChangedScope()
    {
        $availability = $this->readTestAvailability();
        $availability->scope['preferences']['appointment']['endInDaysDefault'] = 63;
        $availability->scope = (new \BO\Zmsdb\Scope())->updateEntity($availability->scope->id, $availability->scope, 1);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on changed scope");
    }

    public function testNoChange2()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots without change");
    }


    public function testChangedDayoff()
    {
        $availability = new \BO\Zmsentities\Availability(['id' => static::TEST_AVAILABILITY_ID]);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $dayoff = new \BO\Zmsentities\Dayoff([
            'name' => 'unittest',
            'date' => $now->modify('+1 day')->getTimestamp(),
        ]);
        (new \BO\Zmsdb\DayOff())->writeCommonDayoffsByYear([$dayoff], 2016);
        $availability = $this->readTestAvailability();
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on changed dayoff");
    }

    public function testChangedDayoffNotaffecting()
    {
        $availability = new \BO\Zmsentities\Availability(['id' => static::TEST_AVAILABILITY_ID]);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $dayoff = new \BO\Zmsentities\Dayoff([
            'name' => 'unittest',
            'date' => $now->modify('+100 day')->getTimestamp(),
        ]);
        (new \BO\Zmsdb\DayOff())->writeCommonDayoffsByYear([$dayoff], 2016);
        $availability = $this->readTestAvailability();
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(
            $status,
            "Availability should not rebuild slots on changed dayoff without affecting booking time"
        );
    }

    public function testNoChangeByTime()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+1 hour -1 second');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Should not rebuild slots without a change");
        $availability->lastChange = $now->getTimestamp();
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertTrue($status, "If availability is changed, it should rebuild, even a rebuild happened before");
    }

    /**
     * Test with availabilities bookable only on one day
     *
     * @codingStandardsIgnoreStart
     *                timeline   ╠═bookable time═╣
     *                day: -2 . 0 1 2 3 4 5 6 7 8 9
     * now            ├─────────╳─╎───╎─╠═══════╣─╎─────────╼
     * lastchange     ├─────╳─────╎─╠═══════╣─╎───╎─────────╼
     * availability1  ├───────────╳───╎───╎───╎───╎─────────╼ no change
     * availability2  ├───────────────╳───╎───╎───╎─────────╼ rebuild
     * availability3  ├───────────────────╳───╎───╎─────────╼ no change
     * availability4  ├───────────────────────╳───╎─────────╼ rebuild
     * availability5  ├───────────────────────────╳─────────╼ no change
     * @codingStandardsIgnoreEnd
     */
    public function testChangeOutdated()
    {
        $availability = $this->readTestAvailability();
        $lastChange = (new Slot())->readLastChangedTimeByAvailability($availability);
        $now = $lastChange->modify('+2 days');
        $availability['bookable']['startInDays'] = 4;
        $availability['bookable']['endInDays'] = 8;
        $availability1 = clone $availability;
        $availability1->startDate = $now->modify('+1 day')->getTimestamp();
        $availability1->endDate = $now->modify('+1 day')->getTimestamp();
        $availability2 = clone $availability;
        $availability2->startDate = $now->modify('+3 day')->getTimestamp();
        $availability2->endDate = $now->modify('+3 day')->getTimestamp();
        $availability3 = clone $availability;
        $availability3->startDate = $now->modify('+5 day')->getTimestamp();
        $availability3->endDate = $now->modify('+5 day')->getTimestamp();
        $availability4 = clone $availability;
        $availability4->startDate = $now->modify('+7 day')->getTimestamp();
        $availability4->endDate = $now->modify('+7 day')->getTimestamp();
        $availability5 = clone $availability;
        $availability5->startDate = $now->modify('+9 day')->getTimestamp();
        $availability5->endDate = $now->modify('+9 day')->getTimestamp();
        $status = (new Slot())->isAvailabilityOutdated($availability1, $now, $lastChange);
        $this->assertFalse($status, "Should not rebuild in case 1");
        $status = (new Slot())->isAvailabilityOutdated($availability2, $now, $lastChange);
        $this->assertTrue($status, "Should rebuild in case 2");
        $status = (new Slot())->isAvailabilityOutdated($availability3, $now, $lastChange);
        $this->assertFalse($status, "Should not rebuild in case 3");
        $status = (new Slot())->isAvailabilityOutdated($availability4, $now, $lastChange);
        $this->assertTrue($status, "Should rebuild in case 4");
        $status = (new Slot())->isAvailabilityOutdated($availability5, $now, $lastChange);
        $this->assertFalse($status, "Should not rebuild in case 5");
    }

    /**
     * New slots should be calculated only if the daytime is after the opening start time
     */
    public function testChangeByDayTime()
    {
        $availability = $this->readTestAvailability();
        $availability['bookable']['startInDays'] = 4;
        $availability['bookable']['endInDays'] = 7;
        $availability->weekday = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 0,
            'wednesday' => 0,
            'thursday' => 0,
            'friday' => 0,
            'saturday' => 0
        ];
        $availability['startTime'] = '08:00:00';
        $availability['endTime'] = '12:00:00';
        $availability['lastChange'] = $availability->startDate;
        $availability['scope']['lastChange'] = $availability->startDate;
        //var_dump($availability);

        $startDate = (new \DateTimeImmutable())->setTimestamp($availability->startDate);
        $monday = $startDate->modify('next monday');

        $this->assertOutdated(
            $monday->modify('06:00:00'),
            $monday->modify('06:00:00'),
            false, //shouldRebuild
            $availability,
            "Availability should not rebuild slots if time is before start time"
        );
        $this->assertOutdated(
            $monday->modify('06:00:00'),
            $monday->modify('08:00:00'),
            true, //shouldRebuild
            $availability,
            "Availability should rebuild slots at right time"
        );
        $this->assertOutdated(
            $monday->modify('08:00:00'),
            $monday->modify('09:00:00'),
            false, //shouldRebuild
            $availability,
            "Availability should not rebuild slots if it already happened the day"
        );
        $this->assertOutdated(
            $monday->modify('08:00:00'),
            $monday->modify('+1 day 00:00:00'),
            false, //shouldRebuild
            $availability,
            "Availability should not rebuild slots at midnight the following day"
        );
        $this->assertOutdated(
            $monday->modify('08:00:00'),
            $monday->modify('+1 day 09:00:00'),
            false, //shouldRebuild
            $availability,
            "Availability should not rebuild slots at the following day"
        );
        $this->assertOutdated(
            $monday->modify('07:00:00'),
            $monday->modify('09:00:00'),
            true, //shouldRebuild
            $availability,
            "Availability should rebuild slots if lastChange was before opening"
        );
        $this->assertOutdated(
            $monday->modify('-2day 07:00:00'),
            $monday->modify('09:00:00'),
            true, //shouldRebuild
            $availability,
            "Availability should rebuild slots if lastChange was multiple days before"
        );
        $this->assertOutdated(
            $monday->modify('-7day 08:00:00'),
            $monday->modify('09:00:00'),
            true, //shouldRebuild
            $availability,
            "Availability should rebuild slots if lastChange was a week before"
        );
    }

    protected function assertOutdated($lastChange, $now, $shouldRebuild, $availability, $message)
    {
        $availability = clone $availability;
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        if (($status && !$shouldRebuild) || (!$status && $shouldRebuild)) {
            $this->debugOutdated($availability, $now, $lastChange);
        }
        if ($shouldRebuild) {
            $this->assertTrue($status, $message);
        } else {
            $this->assertFalse($status, $message);
        }
    }

    public function testChangeByTime()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+2 day');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
        $lastChange = $now;
        $now = $now->modify('+10 minutes');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        //$this->debugOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots twice");
    }

    public function testNoChangeIntegration()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $status = (new Slot())->writeByAvailability($availability, $now);
        $this->assertFalse($status, "Availability should not rebuild slots without change");
    }

    public function testChangedScopeIntegration()
    {
        $availability = $this->readTestAvailability();
        $availability->scope['preferences']['appointment']['endInDaysDefault'] = 63;
        $availability->scope = (new \BO\Zmsdb\Scope())->updateEntity($availability->scope->id, $availability->scope, 1);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $status = (new Slot())->writeByAvailability($availability, $now);
        //$this->debugOutdated($availability, $now, $now->modify('-1 hour'));
        $this->assertFalse(!$status, "Availability should rebuild slots on changed scope");
    }

    /**
     *  @see https://projekte.berlinonline.de/issues/36088
     */
    public function testChangeOnCancel()
    {
        $now = new \DateTimeImmutable('2016-04-06 07:55:01');
        $availability = $this->readTestAvailability();
        $availability->lastChange = $now->modify('-1 minute')->getTimestamp();
        (new Slot())->perform(
            "UPDATE slot SET updateTimestamp = :dateTime WHERE availabilityID = :availabilityID",
            [
                "availabilityID" => $availability->id,
                "dateTime" => $now->modify('-2 minutes')->format('Y-m-d H:i:s'),
            ]
        );
        (new Slot())->writeCanceledByTimeAndScope($now->modify('+5 minutes'), $availability->scope);
        $lastChange = (new Slot())->readLastChangedTimeByAvailability($availability);
        $this->assertEquals(
            '2016-04-06 07:53:01',
            $lastChange->format('Y-m-d H:i:s'),
            "readLastChangedTimeByAvailability should not return cancelled slots"
        );
        $status = (new Slot())->writeByAvailability($availability, $now);
        //$this->debugOutdated($availability, $now, $now->modify('-1 hour'));
        $this->assertFalse(!$status, "Availability should rebuild slots if newer");
    }

    public function testChangeByTimeIntegration()
    {
        $availability = $this->readTestAvailability();
        $lastChange = (new Slot())->readLastChangedTimeByAvailability($availability);
        $status = (new Slot())->writeByAvailability($availability, static::$now);
        //$this->debugOutdated($availability, $now, $lastChange);
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+1 day');
        $status = (new Slot())->writeByAvailability($availability, $now);
        //$this->debugOutdated($availability, $now, $lastChange);
       
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
    }

    public function testWriteCanceledByTime()
    {
        $scope = new \BO\Zmsentities\Scope(['id' => 141]);
        $dateTime = new \DateTimeImmutable('2016-04-01 11:55:00');
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+7days'));
        $this->assertEquals($slotList[0]['status'], 'free');
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+14days'));
        $this->assertEquals($slotList[0]['status'], 'free');

        // Test change of month
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+28days'));
        $this->assertEquals($slotList[0]['status'], 'free');
        (new Slot())->writeCanceledByTime($dateTime->modify('+32days'));
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+28days'));
        $this->assertEquals($slotList[0]['status'], 'cancelled');
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+35days'));
        $this->assertEquals($slotList[0]['status'], 'free');
    }

    public function testDeleteSlotsOlderThan()
    {
        $scope = new \BO\Zmsentities\Scope(['id' => 141]);
        $dateTime = new \DateTimeImmutable('2016-04-01 11:55:00');
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+7days'));
        $this->assertEquals($slotList[0]['status'], 'free');
        (new Slot())->deleteSlotsOlderThan($dateTime->modify('+8days'));
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+7days'));
        $this->assertEquals(count($slotList), 0);
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+14days'));
        $this->assertEquals($slotList[0]['status'], 'free');

        // Test change of month
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+28days'));
        $this->assertEquals($slotList[0]['status'], 'free');
        (new Slot())->deleteSlotsOlderThan($dateTime->modify('+32days'));
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+28days'));
        $this->assertEquals(count($slotList), 0);
        $slotList = (new Slot())->readRowsByScopeAndDate($scope, $dateTime->modify('+35days'));
        $this->assertEquals($slotList[0]['status'], 'free');
    }

    protected function debugOutdated($availability, $now, $lastChange)
    {
        $proposedChange = new \BO\Zmsdb\Helper\AvailabilitySnapShot($availability, $now);
        $formerChange = new \BO\Zmsdb\Helper\AvailabilitySnapShot($availability, $lastChange);
        $debug = "\n$availability\n";
        $values = "\t%s = %s\n";
        $debug .= sprintf($values, 'now', $now->format('c l'));
        $debug .= sprintf($values, 'last', $lastChange->format('c l'));
        $debug .= sprintf($values, 'Availability(lastChange)', (new \DateTimeImmutable)
            ->setTimestamp($availability->lastChange)->format('c l'));
        $debug .= sprintf($values, 'BookableStart(now)', $availability->getBookableStart($now));
        $debug .= sprintf($values, 'BookableStart(last)', $availability->getBookableStart($lastChange));
        $debug .= sprintf($values, 'BookableEnd(now)', $availability->getBookableEnd($now));
        $debug .= sprintf($values, 'BookableEnd(last)', $availability->getBookableEnd($lastChange));
        $debug .= sprintf($values, 'BookEndTime', $availability->getBookableEnd($now)->modify($now->format('H:i:s')));
        $debug .= sprintf(
            $values,
            'formerChange::isOpenedOnLastBookableDay',
            $formerChange->isOpenedOnLastBookableDay() ? 'true' : 'false'
        );
        $debug .= sprintf(
            $values,
            'formerChange::isTimeOpenedOnLastBookableDay',
            $formerChange->isTimeOpenedOnLastBookableDay() ? 'true' : 'false'
        );
        $debug .= sprintf(
            $values,
            'proposedChange::isOpenedOnLastBookableDay',
            $proposedChange->isOpenedOnLastBookableDay() ? 'true' : 'false'
        );
        $debug .= sprintf(
            $values,
            'proposedChange::isTimeOpenedOnLastBookableDay',
            $proposedChange->isTimeOpenedOnLastBookableDay() ? 'true' : 'false'
        );
        $debug .= sprintf(
            $values,
            'proposedChange::hasBookableDateTimeAfter(formerChange)',
            $proposedChange->hasBookableDateTimeAfter($formerChange->getLastBookableDateTime()) ? 'true' : 'false'
        );
        $debug .= sprintf(
            $values,
            'processingNote',
            (isset($availability['processingNote'])) ? json_encode($availability['processingNote']) : 'none'
        );
        error_log($debug);
    }

    protected function readTestAvailability()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(static::TEST_AVAILABILITY_ID, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $availability->endDate = $now->modify('+1 year')->getTimestamp();
        $availability->weekday = [
            'sunday' => 1,
            'monday' => 1,
            'tuesday' => 1,
            'wednesday' => 1,
            'thursday' => 1,
            'friday' => 1,
            'saturday' => 1
        ];
        return $availability;
    }
}
