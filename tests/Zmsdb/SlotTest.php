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
        $this->assertFalse($status, "Availability should not rebuild slots without change one hour after rebuild");
    }

    public function testChangeByTime()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+2 day');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
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
        $this->assertFalse(!$status, "Availability should rebuild slots on changed scope");
    }

    public function testChangeByTimeIntegration()
    {
        $availability = $this->readTestAvailability();
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $now = $now->modify('+2 day');
        $status = (new Slot())->writeByAvailability($availability, $now);
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
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
