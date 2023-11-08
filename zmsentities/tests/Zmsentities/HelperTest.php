<?php

namespace BO\Zmsentities\Tests;

class HelperTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Dayoff';

    public function testProperty()
    {
        $collection = $this->getExampleList();
        $entity = $collection->getIterator()->current();

        $this->assertTrue($entity->toProperty()->offsetExists('date'), 'Property offset date not exists');
        $this->assertStringContainsString('Karfreitag', $entity->toProperty()->__toString(), 'Property __toString failed');

        try {
            $entity->toProperty()->offsetSet('date', '1458856801');
            $this->fail("Expected exception PropertyOffsetReadOnly not thrown");
        } catch (\BO\Zmsentities\Exception\PropertyOffsetReadOnly $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertStringContainsString('is readonly', $exception->getMessage());
        }

        try {
            $entity->toProperty()->offsetUnset('date');
            $this->fail("Expected exception PropertyOffsetReadOnly not thrown");
        } catch (\BO\Zmsentities\Exception\PropertyOffsetReadOnly $exception) {
            $this->assertEquals(500, $exception->getCode());
            $this->assertStringContainsString('is readonly', $exception->getMessage());
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
        $this->assertStringContainsString('2016-01-31T12:50:00+01:00', $time->__toString(), 'Helper DateTime to string failed');
    }

    public function testSummerTimeChange()
    {
        $dateTimeSummerStart = \BO\Zmsentities\Helper\DateTime::getSummerTimeStartDateTime(2016);
        $this->assertEquals('2016-03-27 03:00:00', $dateTimeSummerStart->format('Y-m-d H:i:s'));

        $dateTimeSummerEnd = \BO\Zmsentities\Helper\DateTime::getSummerTimeEndDateTime(2016);
        $this->assertEquals('2016-10-30 03:00:00', $dateTimeSummerEnd->format('Y-m-d H:i:s'));
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
        return $collection;
    }

    public function testDelegate()
    {
        $process = new \BO\Zmsentities\Process();
        $process->amendment = "old";
        $process->queue->status = 'fake';
        $process->getFirstClient()->familyName = 'Older';
        $delegate = new \BO\Zmsentities\Helper\Delegate($process);
        
        $this->assertEquals('old', $process->amendment);
        $setterAmendment = $delegate->setter('amendment');
        $setterAmendment('new');
        $this->assertEquals('new', $process->amendment);

        $this->assertEquals('fake', $process->queue->status);
        $setterAmendment = $delegate->setter('queue', 'status');
        $setterAmendment('new');
        $this->assertEquals('new', $process->queue->status);

        $this->assertEquals('Older', $process->getFirstClient()->familyName);
        $setterAmendment = $delegate->setter('clients', 0, 'familyName');
        $setterAmendment('Newer');
        $this->assertEquals('Newer', $process->getFirstClient()->familyName);
    }
}
