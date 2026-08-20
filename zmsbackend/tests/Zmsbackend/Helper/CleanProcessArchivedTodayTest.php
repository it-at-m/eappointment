<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\CleanProcessArchivedToday;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived;

class CleanProcessArchivedTodayTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testDeletesRowsBeforeTodayAndKeepsCurrentDay(): void
    {
        $repo = new ProcessStatusArchived();
        $now = new \DateTimeImmutable('2016-04-01 16:00:00');

        $repo->perform(
            'INSERT INTO `buergerarchivtoday`
                (`StandortID`, `Datum`, `Timestamp`, `waiting_time`)
             VALUES
                (141, \'2016-03-31\', \'10:00:00\', 5),
                (141, \'2016-04-01\', \'11:00:00\', 6)'
        );

        $this->assertSame(1, $this->countArchiveTodayByDate($repo, '2016-03-31'));
        $this->assertSame(1, $this->countArchiveTodayByDate($repo, '2016-04-01'));

        $job = new CleanProcessArchivedToday();
        $job->startProcessing(true, $now);

        $this->assertSame(0, $this->countArchiveTodayByDate($repo, '2016-03-31'));
        $this->assertSame(1, $this->countArchiveTodayByDate($repo, '2016-04-01'));
    }

    public function testDryRunDoesNotDelete(): void
    {
        $repo = new ProcessStatusArchived();
        $now = new \DateTimeImmutable('2016-04-01 16:00:00');

        $repo->perform(
            'INSERT INTO `buergerarchivtoday`
                (`StandortID`, `Datum`, `Timestamp`, `waiting_time`)
             VALUES
                (141, \'2016-03-31\', \'10:00:00\', 5)'
        );

        $job = new CleanProcessArchivedToday();
        $job->startProcessing(false, $now);

        $this->assertSame(1, $this->countArchiveTodayByDate($repo, '2016-03-31'));
    }

    private function countArchiveTodayByDate(ProcessStatusArchived $repo, string $date): int
    {
        return (int) $repo->fetchValue(
            'SELECT COUNT(*) FROM `buergerarchivtoday` WHERE `Datum` = :theDay',
            [
                'theDay' => $date,
            ]
        );
    }
}
