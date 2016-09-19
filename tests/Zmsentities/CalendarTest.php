<?php

namespace BO\Zmsentities\Tests;

class CalendarTest extends EntityCommonTests
{
    const FIRST_DAY = '2016-04-01';

    const LAST_DAY = '2016-05-30';

    const PROVIDER = 122217;

    const REQUESTS = 120703;

    public $entityclass = '\BO\Zmsentities\Calendar';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addProvider('dldb', self::PROVIDER);
        $this->assertEquals(2, count($entity->getProviderList()));
        $entity->addRequest('dldb', self::REQUESTS);
        $this->assertEquals(2, count($entity->requests));
        $this->assertInstanceOf(
            '\BO\Zmsentities\Collection\ScopeList',
            $entity->getScopeList(),
            'ScopeList does not exists'
        );

        $time = \DateTime::createFromFormat('Y-m-d', self::FIRST_DAY);
        $date = $entity->getDateTimeFromTs($time->getTimestamp(), $time->getTimezone());
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
        $entity->addFirstAndLastDay(self::FIRST_DAY, self::LAST_DAY);
        $this->assertTrue($entity->getFirstDay()
            ->format('Y-m-d') == self::FIRST_DAY, 'First day does not match');
        $this->assertTrue($entity->getLastDay()
            ->format('Y-m-d') == self::LAST_DAY, 'Last day does not match');

        $firstDay = explode('-', self::FIRST_DAY);

        $this->assertTrue($entity->hasDay(
            $firstDay[0], $firstDay[1], $firstDay[2]),
            'Day '. self::FIRST_DAY .' not found'
        );
        $this->assertFalse($entity->hasDay(
            $firstDay[0], $firstDay[2], $firstDay[1]),
            'Day '. self::FIRST_DAY .' not found'
            );

        $day = $entity->getDay($firstDay[0], $firstDay[1], $firstDay[2]);
        $this->assertTrue('free' == $day->freeAppointments['type'], 'Last day does not match');
        $entity['days'][] = array (
            'day' => date('d'),
            'month' => date('m'),
            'year' => date('Y')
        );
        $day = $entity->getDay(date('Y'), date('m'), date('d'));
        $this->assertTrue('free' == $day->freeAppointments['type'], 'Last day does not match');
    }

    public function testMonthList()
    {
        $entity = (new $this->entityclass())->getExample();
        $monthList = $entity->getMonthList();
        foreach ($monthList as $month) {
            $this->assertInstanceOf('BO\Zmsentities\Helper\DateTime', $month);
        }

        $firstDay = explode('-', self::FIRST_DAY);
        $lastDay = explode('-', self::LAST_DAY);
        $entity['firstDay'] = array ('day' => $lastDay[2], 'month' => $lastDay[1], 'year' => $lastDay[0]);
        $entity['lastDay'] = array ('day' => $firstDay[2], 'month' => $firstDay[1], 'year' => $firstDay[0]);
        $monthList = $entity->getMonthList();
        foreach ($monthList as $month) {
            $this->assertInstanceOf('BO\Zmsentities\Helper\DateTime', $month);
        }
    }
}
