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
        $this->assertTrue('Christi Himmelfahrt' == $collection->getIterator()->current()->name, 'Dayoff list sort by name failed');
        $collection->sortByCustomKey('date');
        $this->assertTrue('Karfreitag' == $collection->getIterator()->current()->name, 'Dayoff list sort by time failed');
    }

    public function testWithNew()
    {
        $collection = $this->getDayOffExampleList();
        $this->assertEquals(6, $collection->count());
        $entity = new $this->entityclass([
            "date" => "08.03.2016",
            "name" => "Internationaler Frauentag"
        ]);
        $collection2 = clone $collection;
        $collection2 = $collection2->addEntity($entity);
        $collection2 = $collection2->withTimestampFromDateformat();
        $collection = $collection->withNew($collection2);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(1457395200, $collection->getFirst()->date);
    }

    public function testHasDatesInYear()
    {
        $collection = $this->getDayOffExampleList();
        $this->assertTrue($collection->testDatesInYear(2016));
    }

    public function testHasDatesInYearFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\DayoffWrongYear');
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

    public function testIsNewerThan()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $availabiliy = new \BO\Zmsentities\Availability();
        $collection = new \BO\Zmsentities\Collection\DayoffList();
        $dayoff = new \BO\Zmsentities\Dayoff(
            [
                "date" => "1458856800",
                "name" => "Karfreitag"
            ]
        );
        $collection[] = $dayoff;
        $dayoff->lastChange = $now->modify('-1 day')->getTimestamp();
        $this->assertFalse($collection->isNewerThan($now), "lastChange is older");
        $dayoff->lastChange = $now->modify('+1 day')->getTimestamp();
        $this->assertTrue($collection->isNewerThan($now), "lastChange is newer");
        $this->assertFalse($collection->isNewerThan($now, $availabiliy, $now), "availability is not bookable");
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
