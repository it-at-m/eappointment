<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Slot;
use \BO\Zmsentities\Collection\SlotList as Collection;

class SlotTest extends Base
{
    public function testChanged()
    {
        $changed = (new Slot())->readLastChangedTime();
        //var_dump($changed->format('c'));
        $this->assertTrue($changed instanceof \DateTimeInterface);
    }

    public function testNoChange()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots without change");
    }

    public function testChangedAvailability()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 0);
        $availability->workstationCount['intern'] = 5;
        $availability = (new \BO\Zmsdb\Availability())->updateEntity(68985, $availability, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on change");
    }

    public function testChangedScope()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 1);
        $availability->scope['preferences']['appointment']['endInDaysDefault'] = 63;
        $availability->scope = (new \BO\Zmsdb\Scope())->updateEntity($availability->scope->id, $availability->scope, 1);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on changed scope");
    }

    public function testNoChange2()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots without change");
    }


    public function testChangedDayoff()
    {
        $availability = new \BO\Zmsentities\Availability(['id' => 68985]);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $dayoff = new \BO\Zmsentities\Dayoff([
            'name' => 'unittest',
            'date' => $now->modify('+1 day')->getTimestamp(),
        ]);
        (new \BO\Zmsdb\DayOff())->writeCommonDayoffsByYear([$dayoff], 2016);
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots on changed dayoff");
    }

    public function testChangedDayoffNotaffecting()
    {
        $availability = new \BO\Zmsentities\Availability(['id' => 68985]);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $dayoff = new \BO\Zmsentities\Dayoff([
            'name' => 'unittest',
            'date' => $now->modify('+100 day')->getTimestamp(),
        ]);
        (new \BO\Zmsdb\DayOff())->writeCommonDayoffsByYear([$dayoff], 2016);
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $lastChange = $now->modify("-1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(
            $status,
            "Availability should not rebuild slots on changed dayoff without affecting booking time"
        );
    }

    public function testNoChangeByTime()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now;
        $now = $now->modify('+1 hour -1 second');
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse($status, "Availability should not rebuild slots without change one hour after rebuild");
    }

    public function testChangeByTime()
    {
        $availability = (new \BO\Zmsdb\Availability())->readEntity(68985, 2);
        $now = (new Slot())->readLastChangedTimeByAvailability($availability);
        $lastChange = $now->modify("-1 hour +1 second");
        $status = (new Slot())->isAvailabilityOutdated($availability, $now, $lastChange);
        $this->assertFalse(!$status, "Availability should rebuild slots if time allows new slots");
    }
}
