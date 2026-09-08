<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\CalculateDailyWaitingStatisticByCron;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived;

class CalculateDailyWaitingStatisticByCronTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const SCOPE_ID = 141;
    private const STAT_DATE = '2016-03-31';

    public function testWritesAveragesFromArchiveAndKeepsEstimatedColumns(): void
    {
        $repo = new ProcessStatusArchived();
        $this->insertArchiveRow($repo, '09:15:00', 1, 10, 4, 0);
        $this->insertArchiveRow($repo, '09:45:00', 1, 20, 6, 1);
        $this->insertArchiveRow($repo, '10:30:00', 0, 12, 3, 0);
        $this->insertArchiveRow($repo, '11:00:00', 1, 0, 9, 0);
        $this->insertArchiveRow($repo, '12:00:00', 1, null, 9, 0);

        $repo->perform(
            'INSERT INTO `wartenrstatistik`
                (`standortid`, `datum`, `hour_09_estimated_waiting_time_appointment`)
             VALUES
                (:sid, :d, 42)',
            [
                'sid' => self::SCOPE_ID,
                'd' => self::STAT_DATE,
            ]
        );

        (new CalculateDailyWaitingStatisticByCron())->run(
            new \DateTimeImmutable(self::STAT_DATE . ' 00:00:00'),
            true
        );

        $row = $this->fetchStatisticRow($repo);
        $this->assertSame(2, (int) $row['hour_09_waiting_count_appointment']);
        $this->assertEqualsWithDelta(15.0, (float) $row['hour_09_waiting_time_appointment'], 0.01);
        $this->assertEqualsWithDelta(5.0, (float) $row['hour_09_way_time_appointment'], 0.01);
        $this->assertSame(1, (int) $row['hour_10_waiting_count_spontaneous']);
        $this->assertEqualsWithDelta(12.0, (float) $row['hour_10_waiting_time_spontaneous'], 0.01);
        $this->assertEqualsWithDelta(3.0, (float) $row['hour_10_way_time_spontaneous'], 0.01);
        $this->assertSame(0, (int) $row['hour_11_waiting_count_appointment']);
        $this->assertSame(0, (int) $row['hour_12_waiting_count_appointment']);
        $this->assertEqualsWithDelta(42.0, (float) $row['hour_09_estimated_waiting_time_appointment'], 0.01);
        $this->assertSame(1, $this->countStatisticRows($repo));
    }

    public function testSecondRunUpdatesTheSameRow(): void
    {
        $repo = new ProcessStatusArchived();
        $this->insertArchiveRow($repo, '08:00:00', 1, 10, 2, 0);

        $job = new CalculateDailyWaitingStatisticByCron();
        $day = new \DateTimeImmutable(self::STAT_DATE . ' 00:00:00');
        $job->run($day, true);
        $job->run($day, true);

        $this->assertSame(1, $this->countStatisticRows($repo));
        $row = $this->fetchStatisticRow($repo);
        $this->assertSame(1, (int) $row['hour_08_waiting_count_appointment']);
        $this->assertEqualsWithDelta(10.0, (float) $row['hour_08_waiting_time_appointment'], 0.01);
    }

    public function testDryRunDoesNotWrite(): void
    {
        $repo = new ProcessStatusArchived();
        $this->insertArchiveRow($repo, '08:00:00', 1, 10, 2, 0);

        (new CalculateDailyWaitingStatisticByCron())->run(
            new \DateTimeImmutable(self::STAT_DATE . ' 00:00:00'),
            false
        );

        $this->assertSame(0, $this->countStatisticRows($repo));
    }

    public function testSkipsRowsWithoutScope(): void
    {
        $repo = new ProcessStatusArchived();
        $repo->perform(
            'INSERT INTO `buergerarchiv`
                (`StandortID`, `Datum`, `Timestamp`, `mitTermin`, `waiting_time`, `way_time`)
             VALUES
                (0, :d, \'09:00:00\', 1, 10, 2)',
            [
                'd' => self::STAT_DATE,
            ]
        );

        (new CalculateDailyWaitingStatisticByCron())->run(
            new \DateTimeImmutable(self::STAT_DATE . ' 00:00:00'),
            true
        );

        $this->assertSame(0, $this->countStatisticRows($repo));
    }

    private function insertArchiveRow(
        ProcessStatusArchived $repo,
        string $timestamp,
        int $withAppointment,
        ?float $waitingTime,
        float $wayTime,
        int $missed
    ): void {
        $repo->perform(
            'INSERT INTO `buergerarchiv`
                (`StandortID`, `Datum`, `Timestamp`, `mitTermin`, `waiting_time`, `way_time`, `nicht_erschienen`)
             VALUES
                (:sid, :d, :ts, :withAppointment, :waitingTime, :wayTime, :missed)',
            [
                'sid' => self::SCOPE_ID,
                'd' => self::STAT_DATE,
                'ts' => $timestamp,
                'withAppointment' => $withAppointment,
                'waitingTime' => $waitingTime,
                'wayTime' => $wayTime,
                'missed' => $missed,
            ]
        );
    }

    private function fetchStatisticRow(ProcessStatusArchived $repo): array
    {
        $row = $repo->fetchRow(
            'SELECT *
             FROM `wartenrstatistik`
             WHERE `standortid` = :sid
               AND `datum` = :d
             ORDER BY `wartenrstatistikid` ASC
             LIMIT 1',
            [
                'sid' => self::SCOPE_ID,
                'd' => self::STAT_DATE,
            ]
        );

        $this->assertIsArray($row);

        return $row;
    }

    private function countStatisticRows(ProcessStatusArchived $repo): int
    {
        return (int) $repo->fetchValue(
            'SELECT COUNT(*)
             FROM `wartenrstatistik`
             WHERE `standortid` = :sid
               AND `datum` = :d',
            [
                'sid' => self::SCOPE_ID,
                'd' => self::STAT_DATE,
            ]
        );
    }
}
