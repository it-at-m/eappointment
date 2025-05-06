<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar;
use BO\Zmsdb\Exception\OverallCalendar\Conflict;
use DateTimeImmutable;
use DateInterval;

class OverallCalendarTest extends Base
{
    /** Scope 1300 hat nur 1 Seat */
    private const SCOPE = 1300;

    public function testBookAndConflict()
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:30:00';

        /* ---------------------- Happy Path -------------------------------- */
        $cal->book(self::SCOPE, $start, 900001, 2);   // 2 Slots (09:30–09:40)

        $cnt = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchValue(
                'SELECT COUNT(*) FROM gesamtkalender
                       WHERE scope_id = ?
                         AND process_id = 900001',
                [self::SCOPE]
            );
        $this->assertEquals(2, $cnt);

        /* ---------------------- Conflict erwartet ------------------------- */
        $this->expectException(Conflict::class);
        $cal->book(self::SCOPE, $start, 900002, 1);   // gleicher Slot, kein Platz frei
    }

    public function testUnbook()
    {
        $cal   = new OverallCalendar();
        $start = '2016-05-27 09:35:00';

        // erst buchen
        $cal->book(self::SCOPE, $start, 900010, 1);

        // dann wieder stornieren
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
