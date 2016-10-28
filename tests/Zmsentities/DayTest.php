<?php

namespace BO\Zmsentities\Tests;

class DayTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Day';

    public function testBasic()
    {
        $time = new \DateTimeImmutable('2015-05-01 11:55:00');
        $entity = $this->getExample();
        $this->assertContains('Day @2015-11-19 with', $entity->__toString(), 'day to string failed');
        $this->assertTrue('01' == $entity->setDateTime($time)['day'], 'setDateTime to day failed');
    }
}
