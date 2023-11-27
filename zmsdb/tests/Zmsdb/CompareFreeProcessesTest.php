<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar;
use \BO\Zmsdb\ProcessStatusFree;
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

    /**
     * @SuppressWarnings(PHPMD)
     *
     */
    public function testBasic()
    {
        $now = static::$now;
        $dateTime = new \DateTimeImmutable("2016-05-30");
        $freeProcessesDay = 0;
        $freeProcessesDayCallcenter = 0;
        $freeProcessesDayIntern = 0;
        $freeProcessesTime = 0;
        $scopeList = (new Scope())->readList();
        //$scopeList = new \BO\Zmsentities\Collection\ScopeList();
        //$scopeList->addEntity((new Scope())->readEntity(145));
        foreach ($scopeList as $scope) {
            $processAppointments = 0;
            $input = $this->getTestEntity();
            $input->addScope($scope->id);
            /*$day = new \BO\Zmsentities\Day([
                "year" => 2016,
                "month" => 5,
                "day" => 30
            ]);*/
            $calendar = (new Calendar())->readResolvedEntity($input, $now);
            $day = $calendar->getDayByDateTime($dateTime);

            $freeProcessesDay += $day->freeAppointments->public;
            $freeProcessesDayCallcenter += $day->freeAppointments->callcenter;
            $freeProcessesDayIntern += $day->freeAppointments->intern;

            $input2 = $this->getTestEntity();
            $input2->addScope($scope->id);
            $input2->firstDay = $day;
            $freeProcessList = ProcessStatusFree::init()->readFreeProcesses($input2, $now);
            $processAppointments += count($freeProcessList->getAppointmentList());
            $freeProcessesTime += count($freeProcessList->getAppointmentList());

            $this->assertEquals(
                $day->freeAppointments->public,
                $processAppointments,
                "MISMATCH: $scope  calendarDay==$day | freeProcessList(public)==" . $processAppointments
            );
        }
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
