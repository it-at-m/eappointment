<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\CalendarAvailability as Query;

class CalendarAvailabilityTest extends Base
{
    public function testReadAvailability()
    {
        $start = \App::$now;
        $end = (clone $start)->modify('+1 month');
        $result = (new Query())->readFromQuery(
            \App::$now,
            'public',
            0,
            $start->format('Y-m-d'),
            $end->format('Y-m-t'),
            '122217',
            '120703',
            '1',
            'dldb',
            'dldb'
        );

        $this->assertArrayHasKey('days', $result);
        $this->assertArrayHasKey('startDate', $result);
        $this->assertArrayHasKey('endDate', $result);
    }
}
