<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeNotificationorganisation as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeNotificationorganisationTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(71, new DateTime('2016-04-01'), new DateTime('2016-04-01'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(5, count($entity->data));
        $this->assertEquals(71, $entity->data[1][0]); // organisation id
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[1][1]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[1][2]); // department name
        $this->assertEquals("Bürgeramt Heerstraße ", $entity->data[1][3]); // scope name
        $this->assertEquals(14, $entity->data[1][4]); // notifications COUNT
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(71, $entity->data[0][0]); // department id
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->data[0][3]); //department name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(71);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(2, count($entity->data));
    }
}
