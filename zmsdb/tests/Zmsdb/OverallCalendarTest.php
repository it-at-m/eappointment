<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar;
use DateTimeImmutable;

class OverallCalendarTest extends Base
{
    private const SCOPE = 101;

    public function testBookAndConflict(): void
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:30:00';

        $cal->book(self::SCOPE, $start, 900001, 2);

        $cnt = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchValue(
            'SELECT COUNT(*) FROM overall_calendar
              WHERE scope_id = ? AND process_id = 900001',
            [self::SCOPE]
        );
        $this->assertSame(2, (int)$cnt, 'exactly 2 slots booked for 900001');

        $cal->book(self::SCOPE, $start, 900002, 1);

        $cntConflict = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchValue(
            'SELECT COUNT(*) FROM overall_calendar
              WHERE scope_id = ? AND process_id = 900002',
            [self::SCOPE]
        );
        $this->assertSame(
            0,
            (int)$cntConflict,
            'second booking must not create additional slots'
        );

        $cntStill = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchValue(
            'SELECT COUNT(*) FROM overall_calendar
              WHERE scope_id = ? AND process_id = 900001',
            [self::SCOPE]
        );
        $this->assertSame(
            2,
            (int)$cntStill,
            'original booking remains intact'
        );
    }

    public function testUnbook(): void
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:35:00';

        $cal->book(self::SCOPE, $start, 900010, 1);

        $cal->unbook(self::SCOPE, 900010);

        $row = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchOne(
            'SELECT status, process_id
               FROM overall_calendar
              WHERE scope_id = ? AND time = ?',
            [self::SCOPE, $start]
        );

        $this->assertSame('cancelled', $row['status'], 'nach dem Unbook muss der Slot als cancelled markiert sein');
        $this->assertNull($row['process_id']);
    }
}
