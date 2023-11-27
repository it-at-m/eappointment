<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeSlotscope as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeSlotscopeTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(40, count($entity->data));
        $this->assertEquals(105, $entity->data[0][2]); // booked slots
        $this->assertEquals(105, $entity->data[0][3]); // planned slots
        $this->assertEquals(22, $entity->data[39][2]); // booked slots
        $this->assertEquals(94, $entity->data[39][3]); // planned slots
    }

    public function testSubject()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(44, count($entity->data));
        $this->assertEquals(106, $entity->data[0][0]); // scope id
        $this->assertEquals('2016-04-01', $entity->data[0][1]); // period start
        $this->assertEquals('2016-05-26', $entity->data[0][2]); // period end

    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals("Slotbelegung ", $entity->title);
        $this->assertEquals("day", $entity->period);
    }
}
