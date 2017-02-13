<?php

namespace BO\Zmsentities\Tests;

class HelperTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Dayoff';

    public function testProperty()
    {
        $collection = $this->getExampleList();
        $entity = reset($collection);

        $this->assertTrue($entity->toProperty()->offsetExists('date'), 'Property offset date not exists');
        $this->assertContains('Karfreitag', $entity->toProperty()->__toString(), 'Property __toString failed');

        try {
            $entity->toProperty()->offsetSet('date', '1458856801');
            $this->fail("Expected exception PropertyOffsetReadOnly not thrown");
        } catch (\BO\Zmsentities\Exception\PropertyOffsetReadOnly $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertContains('is readonly', $exception->getMessage());
        }

        try {
            $entity->toProperty()->offsetUnset('date');
            $this->fail("Expected exception PropertyOffsetReadOnly not thrown");
        } catch (\BO\Zmsentities\Exception\PropertyOffsetReadOnly $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertContains('is readonly', $exception->getMessage());
        }
    }

    public function testDateTime()
    {
        $entity = new \BO\Zmsentities\Availability();
        $time = \BO\Zmsentities\Helper\DateTime::create(
            new \DateTime('2016-01-01 12:50:00'),
            new \DateTimeZone('Europe/Berlin')
        );
        $this->assertFalse(
            $entity->hasDate($time, $time),
            'Helper DateTime::create failed'
        );

        $time = \BO\Zmsentities\Helper\DateTime::create(
            new \BO\Zmsentities\Helper\DateTime('2016-01-31 12:50:00'),
            new \DateTimeZone('Europe/Berlin')
        );
        $this->assertFalse(
            $entity->hasDate($time, $time),
            'Helper DateTime::create failed'
        );

        $this->assertTrue($time->isLastWeekOfMonth(), 'Helper DateTime is not last week of month');
        $this->assertContains('2016-01-31T12:50:00+01:00', $time->__toString(), 'Helper DateTime to string failed');
    }

    protected function getExampleList()
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

        $collection = new \BO\Zmsentities\Collection\DayoffList();
        foreach ($list as $dayOffData) {
            $collection->addEntity(new \BO\Zmsentities\Dayoff($dayOffData));
        }
        return $collection->sortByTimeKey();
    }
}
