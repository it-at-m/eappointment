<?php

namespace BO\Zmsentities\Tests;

class DayTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Day';

    public function testBasic()
    {
        $entity = $this->getExample();
        var_dump($entity->__toString());
        $this->assertContains('Day @2015-11-19 with', $entity->__toString(), 'day to string failed');
    }
}
