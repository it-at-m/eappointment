<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class BAllTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,122238,122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        317869,324433,325341,324434,122281,324414,122283,122279,122246,122251,
        122257,122208,122226
    ];


    /**
     * Test performance for "berlinweite Suche"
     *
     */
    public function testOverall()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $input = $this->getTestEntity();
        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
        }
        $entity = (new Query())->readResolvedEntity($input, $now);
        $this->assertEquals(0, count($entity['freeProcesses']));
        //$this->dumpProfiler();
        /*
        $testExport = [];
        foreach ($entity->days as $key => $day) {
            $day->freeAppointments = $day->freeAppointments->getArrayCopy();
            $testExport[$key] = $day->getArrayCopy();
        }
        var_export($testExport);
         */
        $dayList  = include('fixtures/BATest_daylist.php');
        foreach ($entity->days as $day) {
            $key = str_pad($day->day, 2, '0', STR_PAD_LEFT)
                . "-"
                . str_pad($day->month, 2, '0', STR_PAD_LEFT)
                . "-$day->year";
            $testDay = new \BO\Zmsentities\Day($dayList[$key]);
            $message = "Day $key has different value for ";
            $testAppointments = $testDay->freeAppointments;
            $dayAppointments = $day->freeAppointments;
            $this->assertEquals($testAppointments->intern, $dayAppointments->intern, $message . "intern");
            $this->assertEquals($testAppointments->callcenter, $dayAppointments->callcenter, $message . "callcenter");
            $this->assertEquals($testAppointments->public, $dayAppointments->public, $message . "public");
        }
    }

    protected function getTestEntity()
    {
        $time = new \DateTimeImmutable("2016-04-01 11:55");
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
        ));
        return $input;
    }
}
