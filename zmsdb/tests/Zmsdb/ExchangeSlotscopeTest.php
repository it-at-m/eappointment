<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeSlotscope as Query;

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
        $this->assertGreaterThanOrEqual(44, count($entity->data));

        $byScope = [];
        foreach ($entity->data as $row) {
            $byScope[(int) $row[0]] = $row;
        }

        $this->assertArrayHasKey(141, $byScope);
        $this->assertEquals('2016-03-15', $byScope[141][1]);
        $this->assertEquals('2016-04-02', $byScope[141][2]);
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
