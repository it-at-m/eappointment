<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar;
use BO\Zmsdb\Exception\OverallCalendar\Conflict;
use DateTimeImmutable;
use DateInterval;

class OverallCalendarTest extends Base
{
    private const SCOPE = 1300;

    public function testBookAndConflict()
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:30:00';

        $cal->book(self::SCOPE, $start, 900001, 2);

        $cnt = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchValue(
                'SELECT COUNT(*) FROM gesamtkalender
                       WHERE scope_id = ?
                         AND process_id = 900001',
                [self::SCOPE]
            );
        $this->assertEquals(2, $cnt);

        $this->expectException(Conflict::class);
        $cal->book(self::SCOPE, $start, 900002, 1);
    }

    public function testUnbook()
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:35:00';

        $cal->book(self::SCOPE, $start, 900010, 1);

        $cal->unbook(self::SCOPE, 900010);

        $row = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchOne(
                'SELECT status, process_id
                         FROM gesamtkalender
                        WHERE scope_id = ?
                          AND time      = ?',
                [self::SCOPE, $start]
            );

        $this->assertEquals('free',  $row['status']);
        $this->assertNull($row['process_id']);
    }
}
