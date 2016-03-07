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
        //var_dump($entity);
        $this->assertTrue($entity->hasDate($time));
    }
}
