<?php

namespace BO\Zmsentities\Tests;

class AvailabilityTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Availability';

    public function testHasDay()
    {
        $entity = new $this->entityclass();
        $time = new \DateTimeImmutable('2016-01-01 12:34:56');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")->format('H:i');
        $entity['weekday']['friday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        //var_dump($entity);
        $this->assertTrue($entity->hasDate($time));
        $this->assertFalse(
            $entity->hasDate($time->modify('+3days')),
            'Availability should not be valid on a monday if only friday is given'
        );
        $entity['weekday']['monday'] = 1;
        $this->assertTrue(
            $entity->hasDate($time->modify('+3days')),
            'Availability should be valid on a monday if friday and monday is given'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+1week')),
            'Availability should not be valid in the first week afterwards if repeating is set to every 2 weeks'
        );
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week')),
            'Availability should be valid in the second week afterwards'
        );
        $entity['repeat']['weekOfMonth'] = 2;
        $this->assertTrue(
            $entity->hasDate($time->modify('+1week')),
            'Availability should be valid in the second week of the month'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+3week')),
            'Availability should be valid in the third week of the month'
        );
        $this->assertFalse(
            $entity->isBookable($time, $time),
            'Availability default should be, that you cannot reserve an appointment for today'
        );
        $this->assertTrue(
            $entity->isBookable($time->modify('+1day'), $time),
            'Availability default should be, that you cannot reserve an appointment for the next day'
        );
    }
}
