<?php

namespace BO\Zmsentities\Tests;

class SlotTest extends EntityCommonTests
{

    public $entityclass = '\BO\Zmsentities\Slot';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $time = \BO\Zmsentities\Helper\DateTime::create($availability['startTime']);

        $this->assertTrue('0:00' == $entity->getTimeString(), 'Slottime should be empty');

        $entity->time = $time;
        $this->assertTrue(
            '10:00' == $entity->getTimeString(),
            'Slottime should instance of DateTimeInterface'
        );

        $entity->setTime($time);
        $this->assertTrue($entity->time == $entity->getTimeString(), 'Slottime does not match');
        $this->assertTrue($entity->hasTime(), 'Slottime 10:00 missed');

        $this->assertTrue(
            'Slot sum@10:00 p/c/i=3/8/10' == $entity->__toString(),
            'SlotTime String does not match'
        );
    }
}
