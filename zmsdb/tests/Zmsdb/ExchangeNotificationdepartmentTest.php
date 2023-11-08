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
        $this->assertEquals(4, count($entity->data)); //all scopes in department
        $this->assertEquals(74, $entity->data[0][0]); // department id
        $this->assertEquals("2016-04-01", $entity->data[2][1]); // date
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[1][2]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[0][3]); // department name
        $this->assertEquals("Bürgeramt Heerstraße ", $entity->data[2][4]); // scope name
        $this->assertEquals(134, $entity->data[2][5]); // notifications COUNT (grouped by scope name)
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(74, $entity->data[0][0]); // department id
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', $entity->data[0][3]); //department name
        $this->assertStringContainsString('Bürgeramt', $entity->data[0][4]); //department name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(3, count($entity->data));
    }
}
