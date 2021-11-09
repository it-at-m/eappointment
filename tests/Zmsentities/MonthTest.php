<?php

namespace BO\Zmsentities\Tests;

class MonthTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-11-31';

    public $entityclass = '\BO\Zmsentities\Month';

    public function testBasic()
    {
        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        $entity = (new $this->entityclass())->getExample();
        foreach ($entity->days as $day) {
            $day = new \BO\Zmsentities\Day($day);
            $day->getWithStatus('public', $time);
            if ($day->isBookable()) {
                $this->assertTrue(
                    $day->year .'-'. $day->month .'-'. $day->day == self::FIRST_DAY,
                    'Bookable day '. self::FIRST_DAY. ' exptected'
                );
            }
            $this->assertInstanceOf('BO\Zmsentities\Day', $day);
        }

        $firstDay = $entity->getFirstDay();
        $this->assertEquals('2015-11-01', $firstDay->format('Y-m-d'));
        $this->assertEquals(1, $entity->getDayList()->count());

        $dayList = new \BO\Zmsentities\Collection\DayList();
        $dayList->addEntity((new \BO\Zmsentities\Day())->setDateTime($time));
        $entity->setDays($dayList);
    }

    public function testGetDayList()
    {
        $entity = $this->getExample();
        $dayListUpdateDayStatus = new \BO\Zmsentities\Collection\DayList(
            [
                [
                    "year" => 2015,
                    "month" => 11,
                    "day" => 20,
                    "status" => "bookable"
                ]
            ]
        );
        $dayListToSetResolvedDayList = new \BO\Zmsentities\Collection\DayList(
            [
                [
                    "year" => 2015,
                    "month" => 11,
                    "day" => 19,
                ],
                [
                    "year" => 2015,
                    "month" => 11,
                    "day" => 20
                ]
            ]
        );
        $entity->days = [
            [
                "year" => 2015,
                "month" => 11,
                "day" => 19,
            ],
            [
                "year" => 2015,
                "month" => 11,
                "day" => 20
            ]
        ];
        $this->assertEquals(2, $entity->getDayList()->count());

        $entity->setDays($dayListToSetResolvedDayList);
        $this->assertEquals('notBookable', $entity->getDayList()->getLast()->status);

        $entity->setDays($dayListUpdateDayStatus);
        $this->assertEquals('bookable', $entity->getDayList()->getLast()->status);
    }
}
