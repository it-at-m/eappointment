<?php

namespace BO\Zmsdb\Tests;

use PHPUnit\Framework\TestCase;

class OverallCalendarTest extends TestCase
{
    const SCOPE = 1300;
    public function testBookAndConflict()
    {
        $calendar = new \BO\Zmsdb\OverallCalendar();
        $start = '2016-05-27 09:35:00';

        $calendar->book(self::SCOPE, $start, 900001, 2);

        $connection = \BO\Zmsdb\Connection\Select::getReadConnection();
        $count = $connection->fetchValue(
            'SELECT COUNT(*) FROM gesamtkalender
             WHERE scope_id = ?
               AND process_id = 900001',
            [self::SCOPE]
        );
        $this->assertEquals(2, $count, 'First booking should create 2 slots for process_id 900001');

        $calendar->book(self::SCOPE, $start, 900002, 1);

        $count = $connection->fetchValue(
            'SELECT COUNT(*) FROM gesamtkalender
             WHERE scope_id = ?
               AND process_id = 900002',
            [self::SCOPE]
        );
        $this->assertEquals(0, $count, 'Second booking should not create any slots due to conflict');
    }

    public function testUnbook()
    {
        $cal = new \BO\Zmsdb\OverallCalendar();
        $start = '2016-05-27 09:35:00';

        $cal->book(self::SCOPE, $start, 900010, 1);
        $cal->unbook(self::SCOPE, 900010);

        $row = \BO\Zmsdb\Connection\Select::getWriteConnection()
            ->fetchOne(
                'SELECT status, process_id
                 FROM gesamtkalender
                WHERE scope_id = ?
                  AND time = ?',
                [self::SCOPE, $start]
            );

        $this->assertEquals('free', $row['status'], 'Slot should be free after unbooking');
        $this->assertNull($row['process_id'], 'Process ID should be null after unbooking');
    }
}
