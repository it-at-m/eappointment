<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Slot;
use \BO\Zmsentities\Collection\SlotList as Collection;

class SlotTest extends Base
{
    const TEST_AVAILABILITY_ID = 68985;

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
        $availability['bookable']['endInDays'] = 8;
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $now = $now->modify('+1 day');
        $now = $now->modify('06:00:00');
        $lastChange = $now;
        $now = $now->modify('07:00:00');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        //$this->debugOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots if time is before start time");
        $now = $now->modify('08:00:00');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        //$this->debugOutdated($availability, $now, $lastChange);
        $this->assertTrue($status, "Availability should rebuild slots at right time");
        $lastChange = $now;
        $now = $now->modify('09:00:00');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        //$this->debugOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots if it already happened the day");
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

    public function testChangeByTimeIntegration()
    {
        $availability = $this->readTestAvailability();
        $lastChange = (new Slot())->readLastChangedTimeByAvailability($availability);
        $now = new \DateTimeImmutable();
        $status = (new Slot())->writeByAvailability($availability, $now);
        //$this->debugOutdated($availability, $now, $lastChange);
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+1 day');
        $status = (new Slot())->writeByAvailability($availability, $now);
        //$this->debugOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
    }

    protected function debugOutdated($availability, $now, $lastChange)
    {
        $debug = "\n$availability\n";
        $values = "\t%s\r\t\t\t= %s\n";
        $debug .= sprintf($values, 'now', $now->format('c'));
        $debug .= sprintf($values, 'lastChange', $lastChange->format('c'));
        $debug .= sprintf($values, 'BookableStart(now)', $availability->getBookableStart($now));
        $debug .= sprintf($values, 'BookableStart(las)', $availability->getBookableStart($lastChange));
        $debug .= sprintf($values, 'BookableEnd(now)', $availability->getBookableEnd($now));
        $debug .= sprintf($values, 'BookableEnd(las)', $availability->getBookableEnd($lastChange));
        $debug .= sprintf($values, 'BookEndTime', $availability->getBookableEnd($now)->modify($now->format('H:i:s')));
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
