<?php

namespace BO\Zmsentities\Tests;

class CalendarTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-12-31';

    const PROVIDER = 122217;

    const CLUSTER = 109;

    const REQUESTS = 120703;

    public $entityclass = '\BO\Zmsentities\Calendar';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        $date = $entity->getDateTimeFromTs($time->getTimestamp(), $time->getTimezone());
        $day = $entity->getDayByDateTime($date);
        $this->assertInstanceOf('\BO\Zmsentities\Day', $day, 'Day is not instance of \BO\Zmsentites\Day');
        $entity->addDates($time->getTimestamp(), $time, $time->getTimezone()->getName());
        $entity->addProvider('dldb', self::PROVIDER);
        $this->assertEquals(2, count($entity->getProviderList()));
        $entity->addRequest('dldb', self::REQUESTS);
        $this->assertEquals(2, count($entity->requests));
        $entity->addCluster('dldb', self::CLUSTER);
        $this->assertEquals(2, count($entity->clusters));
        $this->assertInstanceOf(
            '\BO\Zmsentities\Collection\ScopeList',
            $entity->getScopeList(),
            'ScopeList does not exists'
        );


        $this->assertTrue(self::FIRST_DAY == $date->format('Y-m-d'), 'Getting date from timestamp failed');
    }

    public function testDays()
    {
        $entity = (new $this->entityclass())->getExample();
        unset($entity['firstDay']);
        unset($entity['lastDay']);
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == date('Y-m-d'), 'First day does not match');
        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == date('Y-m-d'), 'First day does not match');

        $timeZone = new \DateTimeZone('Europe/Berlin');
        $date = \BO\Zmsentities\Helper\DateTime::create(self::FIRST_DAY, $timeZone);
        $entity->addFirstAndLastDay($date->getTimestamp(), $date->getTimezone()->getName());
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == self::FIRST_DAY, 'First day does not match');

        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == self::LAST_DAY, 'Last day does not match');

        $firstDay = explode('-', self::FIRST_DAY);
        $this->assertTrue($entity->hasDay(
            $firstDay[0], $firstDay[1], $firstDay[2]),
            'Day '. self::FIRST_DAY .' should be found'
        );

        $day = $entity->getDay($firstDay[0], $firstDay[1], $firstDay[2]);
        $this->assertTrue('free' == $day->freeAppointments['type'], 'Last day does not match');
        $day = $entity->getDay(date('Y'), date('m'), date('d'));
        $this->assertTrue($entity->hasDay($day->year, $day->month, $day->day));
        $this->assertFalse($entity->hasDay('2015', '11', '20'));

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
        $entity['firstDay'] = array ('day' => $lastDay[2], 'month' => $lastDay[1], 'year' => $lastDay[0]);
        $entity['lastDay'] = array ('day' => $firstDay[2], 'month' => $firstDay[1], 'year' => $firstDay[0]);

        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        $monthList = $entity->getMonthListWithStatedDays($time);
        foreach ($monthList as $month) {
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
}
