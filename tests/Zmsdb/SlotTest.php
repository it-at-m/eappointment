<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Slot;
use \BO\Zmsentities\Collection\SlotList as Collection;

class SlotTest extends Base
{
    public function testChanged()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $availability = new \BO\Zmsentities\Availability([
            'weekday' => ['friday' => 1],
            'workstationCount' => ['intern' => 1],
            'startDate' => $now->modify('0:00:00')->getTimestamp(),
            'endDate' => $now->modify('0:00:00')->getTimestamp(),
        ]);
        (new Slot())->writeByAvailability($availability, $now);
        $changed = (new Slot())->readLastChangedTime();
        var_dump($changed->format('c'));
    }
}
