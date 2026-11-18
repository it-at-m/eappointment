<?php

namespace BO\Zmsbackend\Tests\BAll\Service;

use \BO\Zmsbackend\Calendar\Service\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class BAllTest extends \BO\Zmsbackend\Tests\Service\Base
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
        $mismatches = [];
        foreach ($this->fullProviderIdList as $providerId) {
            $input = $this->getTestEntity();
            $input->addProvider('dldb', $providerId);
            $entity = (new Query())->readResolvedEntity($input, static::$now);
            $this->assertEquals(0, count($entity['freeProcesses']));
           
            $this->writeTestExport($entity, 'provider' . $providerId . '_daylist.php');
            $dayList  = include($this->getFixturePath('provider' . $providerId . '_daylist.php'));
            foreach ($entity->days as $day) {
                $key = $day->getDayHash();
                if (!array_key_exists($key, $dayList)) {
                    $mismatches[] = "Day $key missing for provider=$providerId";
                    continue;
                }
                $testDay = new \BO\Zmsentities\Day($dayList[$key]);
                $testSlots = $testDay->freeAppointments;
                $daySlots = $day->freeAppointments;
                if ((string) $testSlots->intern !== (string) $daySlots->intern) {
                    $mismatches[] = "Day $key provider=$providerId intern expected {$testSlots->intern} actual {$daySlots->intern}";
                }
                if ((string) $testSlots->public !== (string) $daySlots->public) {
                    $mismatches[] = "Day $key provider=$providerId public expected {$testSlots->public} actual {$daySlots->public}";
                }
            }
        }
        $this->assertSame([], $mismatches, implode("\n", $mismatches));
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
        $mismatches = [];
        foreach ($entity->days as $day) {
            $key = str_pad($day->day, 2, '0', STR_PAD_LEFT)
                . "-"
                . str_pad($day->month, 2, '0', STR_PAD_LEFT)
                . "-$day->year";
            if (!array_key_exists($key, $dayList)) {
                $mismatches[] = "Day $key missing from BATest_daylist fixture";
                continue;
            }
            $testDay = new \BO\Zmsentities\Day($dayList[$key]);
            $testSlots = $testDay->freeAppointments;
            $daySlots = $day->freeAppointments;
            if ((string) $testSlots->intern !== (string) $daySlots->intern) {
                $mismatches[] = "Day $key intern expected {$testSlots->intern} actual {$daySlots->intern}";
            }
            if ((string) $testSlots->public !== (string) $daySlots->public) {
                $mismatches[] = "Day $key public expected {$testSlots->public} actual {$daySlots->public}";
            }
            if ((string) $testSlots->type !== (string) $daySlots->type) {
                $mismatches[] = "Day $key type expected {$testSlots->type} actual {$daySlots->type}";
            }
            if ((string) $testDay->status !== (string) $day->status) {
                $mismatches[] = "Day $key status expected {$testDay->status} actual {$day->status}";
            }
        }
        $this->assertSame([], $mismatches, implode("\n", $mismatches));
    }

    /**
     * Regenerate golden daylist fixtures after daylist SQL changes:
     *   BALL_EXPORT=1 vendor/bin/phpunit --filter BAllTest
     */
    protected function writeTestExport(Entity $entity, $filename)
    {
        if (!getenv('BALL_EXPORT')) {
            return;
        }

        $testExport = [];
        $export = "<?php\n\n// @codingStandardsIgnoreFile\nreturn ";
        foreach ($entity->days as $key => $day) {
            $day = clone $day;
            $day->month = str_pad($day->month, 2, '0', STR_PAD_LEFT);
            $day->day = str_pad($day->day, 2, '0', STR_PAD_LEFT);
            $day->freeAppointments->intern = intval($day->freeAppointments->intern);
            $day->freeAppointments->public = intval($day->freeAppointments->public);
            $day->freeAppointments = $day->freeAppointments->getArrayCopy();
            // Slot has no __set_state(); export plain arrays so fixtures can be included.
            $day->allAppointments = $day->allAppointments->getArrayCopy();
            $dayData = $day->getArrayCopy();
            unset($dayData['scopeIDs']);
            $testExport[$key] = $dayData;
        }
        $export .= var_export($testExport, true);
        $export .= ";\n";
        file_put_contents($this->getFixturePath($filename), $export);
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
