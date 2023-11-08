<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class DstTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,122238,122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        317869,324433,325341,324434,122281,324414,122283,122279,122246,122251,
        122257,122208,122226
    ];


    /**
     * Test DST (daylight saving time)
     * In 2016 wintertime begins on 2016-10-30
     * Hint: This test should not have an exception about pre-generated slots
     *       Usually the exceptions occurs if timezone settings do not respect
     *       DST and a date from 2016-10-31 is handled as "2016-10-30 23:00"
     */
    public function testOverall()
    {
        $time = new \DateTimeImmutable("2016-10-01 11:55");
        $input = new Entity(array(
            "firstDay" => [
                "year" => $time->format('Y'),
                "month" => $time->format('m'),
                "day" => $time->format('d')
                ],
            "lastDay" => [
                "year" => $time->modify('+1 month')->format('Y'),
                "month" => $time->modify('+1 month')->format('m'),
                "day" => $time->modify('+1 month')->format('t')
                ],
            "requests" => [
                [
                    "source" => "dldb",
                    "id" => "120703",
                    ],
                ],
            "scopes" => [
                [
                    "id" => "169",
                ],
            ],

        ));
        $entity = (new Query())->readResolvedEntity($input, $time);
        $this->assertEquals(0, count($entity['freeProcesses']));
    }
}
