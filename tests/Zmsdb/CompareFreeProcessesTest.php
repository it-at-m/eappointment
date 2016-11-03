<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Scope;

class CompareFreeProcessesTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,122238,122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        317869,324433,325341,324434,122281,324414,122283,122279,122246,122251,
        122257,122208,122226
    ];

    public function testBasic()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55:00");
        $dateTime = new \DateTimeImmutable("2016-05-30");
        $freeProcessesDay = 0;
        $freeProcessesDayCallcenter = 0;
        $freeProcessesDayIntern = 0;
        $freeProcessesTime = 0;
        //$scopeList = (new Scope())->readList();
        $scopeList = new \BO\Zmsentities\COllection\ScopeList();
        $scopeList->addEntity((new Scope())->readEntity(145));
        foreach ($scopeList as $scope) {
            $processAppointments = 0;
            $input = $this->getTestEntity();
            $input->addScope($scope->id);
            $calendar = (new Calendar())->readResolvedEntity($input, $now);
            $day = $calendar->getDayByDateTime($dateTime);

            $freeProcessesDay += $day->freeAppointments->public;
            $freeProcessesDayCallcenter += $day->freeAppointments->callcenter;
            $freeProcessesDayIntern += $day->freeAppointments->intern;

            $input2 = $this->getTestEntity();
            $input2->addScope($scope->id);
            $input2->firstDay = $day;
            echo "FREE PROCESSES \n";
            $freeProcessList = (new Process())->readFreeProcesses($input2, $now);
            foreach ($freeProcessList as $process) {
                $processAppointments += count($process->appointments);
                $freeProcessesTime += count($process->appointments);
            }

            if ( $day->freeAppointments->public != $processAppointments) {
                var_dump('Standort: '. $scope->id . ' (provider '. $scope->provider['id'] .') on calendarDay: '. $day->freeAppointments->public .' | on freeProcessList: '. $processAppointments);
            }

            /*
            $this->assertTrue(
                $day->freeAppointments->public == $processAppointments,
                'Standort: '. $scope->id . ' on calendarDay: '. $day->freeAppointments->public .' | on freeProcessList: '. $processAppointments
            );
            */
        }

        /*
        $input = $this->getTestEntity();
        $input2 = $this->getTestEntity();

        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
            $input2->addProvider('dldb', $providerId);
        }
        $calendar = (new Calendar())->readResolvedEntity($input, $now);
        $day = $calendar->getDayByDateTime($dateTime);
        $input2->firstDay = $day;
        $freeProcessList = (new Process())->readFreeProcesses($input2, $now);
        var_dump('Day by Provider:' .(string)$day . ' | '. count($freeProcessList));

        var_dump('Gesamt DaySelect/TimeSelect: ' . $freeProcessesDay .' / '. $freeProcessesTime);
        var_dump('Gesamt Intern DaySelect/TimeSelect: ' . $freeProcessesDayIntern);
        var_dump('Gesamt Callcenter DaySelect/TimeSelect: ' . $freeProcessesDayCallcenter);
        */
    }

    protected function getTestEntity()
    {
        $input = new \BO\Zmsentities\Calendar(array(
            "firstDay" => [
                "year" => 2016,
                "month" => 5,
                "day" => 1
            ],
            "lastDay" => [
                "year" => 2016,
                "month" => 5,
                "day" => 31
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
