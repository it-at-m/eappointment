<?php

namespace BO\Zmsentities\Tests;

class CalendarTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-12-31';

    const PROVIDER = 122217;

    const SCOPE = 141;

    const CLUSTER = 109;

    const REQUESTS = 120703;

    public $entityclass = '\BO\Zmsentities\Calendar';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        $date = $time;
        $day = $entity->getDayByDateTime($date);
        $this->assertInstanceOf('\BO\Zmsentities\Day', $day, 'Day is not instance of \BO\Zmsentites\Day');
        unset($entity['firstDay']);
        $entity->addDates($time->getTimestamp(), $time, $time->getTimezone()->getName());
        $entity->addProvider('dldb', self::PROVIDER);
        $this->assertEquals(2, count($entity->getProviderList()));
        $entity->addRequest('dldb', self::REQUESTS);
        $this->assertEquals(2, count($entity->getRequestList()));
        $entity->addCluster('dldb', self::CLUSTER);
        $this->assertEquals(2, count($entity->clusters));
        $entity->addScope(self::SCOPE);
        $this->assertEquals(2, count($entity->getScopeList()));
        $this->assertInstanceOf(
            '\BO\Zmsentities\Collection\ScopeList',
            $entity->getScopeList(),
            'ScopeList does not exists'
        );
        $this->assertTrue($entity->isValid());
        $this->assertTrue(self::FIRST_DAY == $date->format('Y-m-d'), 'Getting date from timestamp failed');
    }

    public function testToString()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertStringContainsString('Day notBookable@2015-11-19 with slot#free@0:00 p/c/i=2/0/3', (string) $entity);
        $this->assertStringContainsString('scope#141', (string) $entity);
    }

    public function testHasFirstAndLastDay()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasFirstAndLastDay());

        unset($entity['firstDay']);
        $this->assertFalse($entity->hasFirstAndLastDay());

        $entity = (new $this->entityclass())->getExample();
        unset($entity['lastDay']);
        $this->assertFalse($entity->hasFirstAndLastDay());
    }

    public function testDays()
    {
        $entity = (new $this->entityclass())->getExample();
        unset($entity['firstDay']);
        unset($entity['lastDay']);
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == date('Y-m-d'), 'First day does not match');
        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == date('Y-m-d'), 'Last day does not match');

        $timeZone = new \DateTimeZone('Europe/Berlin');
        $date = \BO\Zmsentities\Helper\DateTime::create(self::FIRST_DAY, $timeZone);
        $entity->addFirstAndLastDay($date->getTimestamp(), $date->getTimezone()->getName());
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == self::FIRST_DAY, 'First day does not match');

        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == self::LAST_DAY, 'Last day does not match');

        $firstDay = explode('-', self::FIRST_DAY);
        $this->assertTrue(
            $entity->hasDay($firstDay[0], $firstDay[1], $firstDay[2]),
            'Day '. self::FIRST_DAY .' should be found'
        );

        $day = $entity->getDay($firstDay[0], $firstDay[1], $firstDay[2]);
        $this->assertTrue('free' == $day->freeAppointments['type'], 'Last day does not match');
        $day = $entity->getDay(date('Y'), date('m'), date('d'));
        $this->assertTrue($entity->hasDay($day->year, $day->month, $day->day));
        $this->assertFalse($entity->hasDay('2015', '11', '20'));

        $entity->setFirstDayTime(\DateTime::createFromFormat('Y-m-d', self::FIRST_DAY));
        $entity->setLastDayTime(\DateTime::createFromFormat('Y-m-d', self::LAST_DAY));
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == self::FIRST_DAY, 'First day does not match');
        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == self::LAST_DAY, 'Last day does not match');
    }

    public function testGetDayList()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->days = [
            [
                "year" => 2015,
                "month" => 11,
                "day" => 19,
            ],
            [
                "year" => 2015,
                "month" => 11,
                "day" => 20,
            ]
        ];
        $this->assertEquals(2, $entity->getDayList()->count());
    }

    public function testWithFilledEmptyDays()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals(1, $entity->getDayList()->count());
        $this->assertEquals(30, $entity->withFilledEmptyDays()->getDayList()->count());
    }

    public function testMonthList()
    {
        $entity = (new $this->entityclass())->getExample();
        $monthList = $entity->getMonthList();
        foreach ($monthList as $month) {
            $this->assertInstanceOf('BO\Zmsentities\Month', $month);
        }

        $firstDay = explode('-', self::FIRST_DAY);
        $lastDay = explode('-', self::LAST_DAY);
        $entity['firstDay'] = array('day' => $lastDay[2], 'month' => $lastDay[1], 'year' => $lastDay[0]);
        $entity['lastDay'] = array('day' => $firstDay[2], 'month' => $firstDay[1], 'year' => $firstDay[0]);

        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        foreach ($entity->getMonthList() as $month) {
            foreach ($month->days as $day) {
                if ($day->isBookable()) {
                    $this->assertTrue(
                        $day->year .'-'. $day->month .'-'. $day->day == self::FIRST_DAY,
                        'Bookable day '. self::FIRST_DAY. ' exptected'
                    );
                }
                $this->assertInstanceOf('BO\Zmsentities\Day', $day);
            }
        }
    }

    public function testLessData()
    {
        $entity = $this->getExample();
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $scope->provider['data'] = array('payment' => 'only cash', 'extra' => 'to remove');
        $scope->dayoff[] = array('name' => '1. Weihnachtsfeiertag');
        $entity->scopes[0] = $scope;
        $entity = $entity->withLessData();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertFalse(isset($entity['days'][0]['allAppointments']), 'Converting to less data failed');
        $this->assertFalse(isset($entity['providers']), 'Converting to less data failed');
        $this->assertFalse(isset($entity['scopes'][0]['provider']['data']['extra']), 'Converting to less data failed');
        $this->assertTrue(isset($entity['scopes'][0]['provider']['data']['payment']), 'Converting to less data failed');
    }
}
