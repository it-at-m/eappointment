<?php

namespace BO\Zmsentities\Tests;

class DayoffTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Dayoff';

    public $collectionclass = '\BO\Zmsentities\Collection\DayoffList';

    public function testCollection()
    {
        $collection = $this->getDayOffExampleList();
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            6 == count($collection),
            'Amount of entities in list is wrong, 6 expected (' .
            count($collection) . ' found)'
        );

        $this->assertTrue(
            1458856800 == $collection->getEntityByName('Karfreitag')->date,
            'Failed to get dayoff by name Karfreitag'
        );

        $this->assertTrue(
            $collection->hasEntityByDate('2016-03-24'),
            'Dayoff list should have entity with date 2016-03-24'
        );

        $this->assertFalse(
            $collection->hasEntityByDate('2015-11-20'),
            'Dayoff list should not have entity with date 2015-11-20'
        );

        $collection->sortByName();
        $this->assertTrue('Christi Himmelfahrt' == reset($collection)->name, 'Dayoff list sort by name failed');
        $collection->sortByCustomKey('date');
        $this->assertTrue('Karfreitag' == reset($collection)->name, 'Dayoff list sort by time failed');
    }

    public function testHasDatesInYear()
    {
        $collection = $this->getDayOffExampleList();
        $this->assertTrue($collection->testDatesInYear(2016));
    }

    public function testHasDatesInYearFailed()
    {
        $this->setExpectedException('\BO\Zmsentities\Exception\DayoffWrongYear');
        $collection = $this->getDayOffExampleList();
        $collection->testDatesInYear(2017);
    }

    public function testDateFormat()
    {
        $list = new $this->collectionclass([
            new $this->entityclass([
                "date" => "16.5.2016",
                "name" => "Pfingsmontag"
            ])
        ]);
        $list = $list->withTimestampFromDateformat();
        $this->assertEquals(1463356800, $list->getFirst()->date);
    }

    protected function getDayOffExampleList()
    {
        $list = [
            [
                "date" => "1458856800",
                "name" => "Karfreitag"
            ],
            [
                "date" => "1459116000",
                "name" => "Ostermontag"
            ],
            [
                "date" => "1462053600",
                "name" => "Maifeiertag"
            ],
            [
                "date" => "1462399200",
                "name" => "Christi Himmelfahrt"
            ],
            [
                "date" => "1463349600",
                "name" => "Pfingstmontag"
            ],
            [
                "date" => "1475445600",
                "name" => "Tag der Deutschen Einheit"
            ]
        ];

        $collection = new $this->collectionclass();
        foreach ($list as $dayOffData) {
            $collection->addEntity(new \BO\Zmsentities\Dayoff($dayOffData));
        }
        return $collection;
    }
}
