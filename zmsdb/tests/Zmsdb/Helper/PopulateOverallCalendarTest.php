<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Helper\PopulateOverallCalendar;
use DateTimeImmutable;

class PopulateOverallCalendarTest extends Base
{
    private const SCOPE = 1301;

    public function testFreeSlotsAreInserted()
    {
        $pop = new PopulateOverallCalendar(true);
        $now = new DateTimeImmutable('2016-05-27 08:00:00');

        \BO\Zmsdb\Connection\Select::getWriteConnection()
            ->perform('DELETE FROM gesamtkalender WHERE scope_id = ?',[self::SCOPE]);

        $pop->writeCalendar($now);


        $cnt = \BO\Zmsdb\Connection\Select::getReadConnection()
            ->fetchValue(
                'SELECT COUNT(*) FROM gesamtkalender
                      WHERE scope_id = ?
                        AND time BETWEEN "2016-05-27 09:00:00"
                                     AND "2016-05-27 09:55:00"',
                [self::SCOPE]
            );

        $this->assertEquals(24, $cnt);
    }
}
