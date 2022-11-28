<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class BAllTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,/*122238,*/122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        122281,324414,122283,122279,122246,122251,122257,122226,
        122208,317869,324433,325341,324434
    ];

    public function testSingle()
    {
        foreach ($this->fullProviderIdList as $providerId) {
            $input = $this->getTestEntity();
            $input->addProvider('dldb', $providerId);
            $entity = (new Query())->readResolvedEntity($input, static::$now);
            $this->assertEquals(0, count($entity['freeProcesses']));
           
            $this->writeTestExport($entity, 'provider' . $providerId . '_daylist.php');
            $dayList  = include($this->getFixturePath('provider' . $providerId . '_daylist.php'));
            foreach ($entity->days as $day) {
                $key = $day->getDayHash();
                $this->assertArrayHasKey($key, $dayList, "Day $key missing for provider=$providerId");
                $testDay = new \BO\Zmsentities\Day($dayList[$key]);
                $message = "Day $key has different value on provider=$providerId for ";
                $testSlots = $testDay->freeAppointments;
                $daySlots = $day->freeAppointments;
                $this->assertEquals($testSlots->intern, $daySlots->intern, $message . "intern");
                $this->assertEquals($testSlots->callcenter, $daySlots->callcenter, $message . "callcenter");
                $this->assertEquals($testSlots->public, $daySlots->public, $message . "public");
            }
        }
    }

    /**
     * Test performance for "berlinweite Suche"
     *
     */
    public function testOverall()
    {
        $input = $this->getTestEntity();
        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
        }
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        $this->assertEquals(0, count($entity['freeProcesses']));
        //var_dump("$entity");
        //$this->dumpProfiler();
        $this->writeTestExport($entity, 'BATest_daylist.php');
        $dayList  = include($this->getFixturePath('BATest_daylist.php'));
        foreach ($entity->days as $day) {
            $key = str_pad($day->day, 2, '0', STR_PAD_LEFT)
                . "-"
                . str_pad($day->month, 2, '0', STR_PAD_LEFT)
                . "-$day->year";
            $testDay = new \BO\Zmsentities\Day($dayList[$key]);
            $message = "Day $key has different value for ";
            $testSlots = $testDay->freeAppointments;
            $daySlots = $day->freeAppointments;
            $this->assertEquals($testSlots->intern, $daySlots->intern, $message . "intern");
            $this->assertEquals($testSlots->callcenter, $daySlots->callcenter, $message . "callcenter");
            $this->assertEquals($testSlots->public, $daySlots->public, $message . "public");
            $this->assertEquals($testSlots->type, $daySlots->type, $message . "type");
            $this->assertEquals($testDay->status, $day->status, $message . "status");
        }
    }

    protected function writeTestExport(Entity $entity, $filename)
    {
        if (getenv("BALL_EXPORT")) {
            $testExport = [];
            $export = "<?php\n\n// @codingStandardsIgnoreFile\nreturn ";
            foreach ($entity->days as $key => $day) {
                $day = clone $day;
                $day->month = str_pad($day->month, 2, '0', STR_PAD_LEFT);
                $day->day = str_pad($day->day, 2, '0', STR_PAD_LEFT);
                $day->freeAppointments->intern = intval($day->freeAppointments->intern);
                $day->freeAppointments->public = intval($day->freeAppointments->public);
                $day->freeAppointments->callcenter = intval($day->freeAppointments->callcenter);
                $day->freeAppointments = $day->freeAppointments->getArrayCopy();
                $testExport[$key] = $day->getArrayCopy();
            }
            $export .= var_export($testExport, true);
            $export .= ";\n";
            file_put_contents($this->getFixturePath($filename), $export);
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
