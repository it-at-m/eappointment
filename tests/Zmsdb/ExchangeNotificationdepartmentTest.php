<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeNotificationdepartment as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeNotificationdepartmentTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(74, new DateTime('2016-04-01'), new DateTime('2016-04-30'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
        $this->assertEquals(74, $entity->data[0][0]); // department id
        $this->assertEquals("2016-04-01", $entity->data[0][1]); // date
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[1][2]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[0][3]); // department name
        $this->assertEquals("Bürgeramt Heerstraße ", $entity->data[0][4]); // scope name
        $this->assertEquals(14, $entity->data[0][5]); // notifications COUNT
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(74, $entity->data[0][0]); // department id
        $this->assertContains('Bürgeramt', $entity->data[0][3]); //department name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(2, count($entity->data));
    }
}
