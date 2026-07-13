<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\CalendarAvailability as Query;

class CalendarAvailabilityTest extends Base
{
    public function testReadAvailability()
    {
        $start = \App::$now;
        $end = (clone $start)->modify('+1 month');
        $result = (new Query())->readFromParams([
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-t'),
            'officeIds' => [122217],
            'serviceIds' => ['120703'],
            'serviceCounts' => ['1'],
            'providerSource' => 'dldb',
            'requestSource' => 'dldb',
        ], \App::$now);

        $this->assertArrayHasKey('days', $result);
        $this->assertArrayHasKey('startDate', $result);
        $this->assertArrayHasKey('endDate', $result);
    }
}
