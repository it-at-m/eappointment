<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverviewCalendar;
use BO\Zmsdb\Connection\Select;
use DateTimeImmutable;

class OverviewCalendarTest extends Base
{
    private const SCOPE1 = 65001;
    private const SCOPE2 = 65002;

    private function fetchByPid(int $pid): ?array
    {
        return Select::getReadConnection()->fetchOne(
            'SELECT * FROM overview_calendar WHERE process_id = ?',
            [$pid]
        ) ?: null;
    }

    private function fetchCount(string $whereSql, array $params = []): int
    {
        $sql = 'SELECT COUNT(*) AS c FROM overview_calendar ' . $whereSql;
        $val = Select::getReadConnection()->fetchValue($sql, $params);
        return (int)$val;
    }

    public function testInsertAndReadRange(): void
    {
        $calendar = new OverviewCalendar();

        $pid   = 900001;
        $start = '2016-05-27 09:30:00';
        $end   = '2016-05-27 09:35:00';

        $calendar->insert(self::SCOPE1, $pid, 'confirmed', $start, $end);

        $row = $this->fetchByPid($pid);
        $this->assertNotNull($row, 'Row must exist after insert');
        $this->assertSame('confirmed', $row['status']);
        $this->assertSame(self::SCOPE1, (int)$row['scope_id']);
        $this->assertSame($start, $row['starts_at']);
        $this->assertSame($end,   $row['ends_at']);

        $res = $calendar->readRange([self::SCOPE1], '2016-05-27 00:00:00', '2016-05-28 00:00:00');
        $this->assertCount(1, $res, 'exactly one confirmed booking visible in range');
        $this->assertSame($pid, (int)$res[0]['process_id']);
        $this->assertSame('confirmed', $res[0]['status']);
        $this->assertSame('T65001', $res[0]['scope_short']);
    }

    public function testCancelByProcessIdempotent(): void
    {
        $calendar = new OverviewCalendar();

        $pid   = 900010;
        $start = '2016-05-27 09:35:00';
        $end   = '2016-05-27 09:40:00';

        $calendar->insert(self::SCOPE1, $pid, 'confirmed', $start, $end);

        $ok1 = $calendar->cancelByProcess($pid);
        $this->assertTrue($ok1, 'first cancel should update 1+ row(s)');

        $row = $this->fetchByPid($pid);
        $this->assertSame('cancelled', $row['status']);

        $ok2 = $calendar->cancelByProcess($pid);
        $this->assertIsBool($ok2);
        $row2 = $this->fetchByPid($pid);
        $this->assertSame('cancelled', $row2['status'], 'status remains cancelled');
    }

    public function testUpdateByProcessOnlyWhenConfirmed(): void
    {
        $calendar = new OverviewCalendar();

        $pid     = 900020;
        $startA  = '2025-05-14 08:00:00';
        $endA    = '2025-05-14 08:15:00';
        $startB  = '2025-05-14 09:00:00';
        $endB    = '2025-05-14 09:20:00';
        $startC  = '2025-05-14 10:00:00';
        $endC    = '2025-05-14 10:30:00';

        $calendar->insert(self::SCOPE2, $pid, 'confirmed', $startA, $endA);

        $calendar->updateByProcess($pid, self::SCOPE2, $startB, $endB);

        $row = $this->fetchByPid($pid);
        $this->assertSame($startB, $row['starts_at']);
        $this->assertSame($endB,   $row['ends_at']);
        $this->assertSame('confirmed', $row['status']);

        $calendar->cancelByProcess($pid);

        $calendar->updateByProcess($pid, self::SCOPE2, $startC, $endC);

        $row2 = $this->fetchByPid($pid);
        $this->assertSame($startB, $row2['starts_at']);
        $this->assertSame($endB,   $row2['ends_at']);
        $this->assertSame('cancelled', $row2['status']);
    }

    public function testReadMaxUpdatedAndGlobal(): void
    {
        $calendar = new OverviewCalendar();

        $max = $calendar->readMaxUpdated([self::SCOPE1, self::SCOPE2], '2016-01-01 00:00:00', '2026-01-01 00:00:00');
        $this->assertNotNull($max, 'should return a timestamp string');

        $maxG = $calendar->readMaxUpdatedGlobal([self::SCOPE1, self::SCOPE2]);
        $this->assertNotNull($maxG, 'should return a timestamp string (global)');
    }

    public function testReadRangeUpdatedAndChangedPidsSince(): void
    {
        $calendar = new OverviewCalendar();

        $pidA = 910001;
        $pidB = 910002;

        $calendar->insert(self::SCOPE2, $pidA, 'confirmed', '2025-05-14 09:10:00', '2025-05-14 09:20:00');
        $calendar->insert(self::SCOPE2, $pidB, 'confirmed', '2025-05-14 09:15:00', '2025-05-14 09:25:00');

        $updatedAfter = '2000-01-01 00:00:00';
        $range = $calendar->readRangeUpdated(
            [self::SCOPE2],
            '2025-05-14 08:00:00',
            '2025-05-14 12:00:00',
            $updatedAfter
        );

        $pids = array_map(fn($r) => (int)$r['process_id'], $range);
        $this->assertContains($pidA, $pids);
        $this->assertContains($pidB, $pids);

        $calendar->cancelByProcess($pidA);

        $changed = $calendar->readChangedProcessIdsSince([self::SCOPE2], $updatedAfter);
        $this->assertContains($pidA, $changed);
        $this->assertContains($pidB, $changed);

        $range2 = $calendar->readRangeUpdated(
            [self::SCOPE2],
            '2025-05-14 08:00:00',
            '2025-05-14 12:00:00',
            $updatedAfter
        );
        $statusesByPid = [];
        foreach ($range2 as $r) {
            $statusesByPid[(int)$r['process_id']] = $r['status'];
        }
        $this->assertSame('cancelled', $statusesByPid[$pidA]);
        $this->assertSame('confirmed', $statusesByPid[$pidB]);
    }

    public function testDeleteOlderThan(): void
    {
        $cal = new OverviewCalendar();

        $pid = 990001;
        $cal->insert(self::SCOPE1, $pid, 'confirmed', '2010-01-01 00:00:00', '2010-01-01 00:05:00');

        $cntBefore = $this->fetchCount('WHERE process_id = ?', [$pid]);
        $this->assertSame(1, $cntBefore, 'row exists before delete');

        $ok = $cal->deleteOlderThan('2010-01-02 00:00:00');
        $this->assertTrue($ok);

        $cntAfter = $this->fetchCount('WHERE process_id = ?', [$pid]);
        $this->assertSame(0, $cntAfter, 'row removed after deleteOlderThan');
    }
}
