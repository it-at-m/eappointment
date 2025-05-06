<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar;
use BO\Zmsdb\Exception\OverallCalendar\Conflict;
use DateTimeImmutable;

/**
 * @covers \BO\Zmsdb\OverallCalendar
 */
class OverallCalendarTest extends Base
{
    /** Scope mit **einem** Seat, vgl. Fixture‑SQL */
    private const SCOPE = 1300;
    private const START = '2016-05-27 09:30:00';

    public function testBookAndConflict()
    {
        $cal   = new OverallCalendar();
        $pidOk = 900001;

        /* ---------- Buchung soll funktionieren ----------------------- */
        $cal->book(self::SCOPE, self::START, $pidOk, 1);

        $row = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchRow('SELECT process_id, status, slots
                          FROM gesamtkalender
                         WHERE scope_id = ? AND time = ?', [
                self::SCOPE,
                self::START,
            ]);

        $this->assertEquals($pidOk,      $row['process_id']);
        $this->assertEquals('termin',    $row['status']);
        $this->assertEquals(1,           $row['slots']);

        /* ---------- zweite Buchung gleicher Slot ⇒ Conflict ---------- */
        $this->expectException(Conflict::class);
        $cal->book(self::SCOPE, self::START, 900002, 1);
    }

    public function testUnbook()
    {
        $cal   = new OverallCalendar();
        $pid   = 900003;

        /* erst buchen … */
        $cal->book(self::SCOPE, self::START, $pid, 1);

        /* … dann stornieren … */
        $cal->unbook(self::SCOPE, $pid);

        $row = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchRow('SELECT process_id, status, slots
                          FROM gesamtkalender
                         WHERE scope_id = ? AND time = ?', [
                self::SCOPE,
                self::START,
            ]);

        $this->assertNull($row['process_id']);
        $this->assertNull($row['slots']);
        $this->assertEquals('free', $row['status']);
    }
}
