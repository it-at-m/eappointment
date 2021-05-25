<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeNotificationscope as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeNotificationscopeTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(141, new DateTime('2016-04-01'), new DateTime('2016-04-01'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(141, $entity->data[0][0]); // scope id
        $this->assertEquals('2016-04-01', $entity->data[0][1]); // date
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[0][2]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[0][3]); // department name
        $this->assertEquals("Bürgeramt Heerstraße ", $entity->data[0][4]); // scope name
        $this->assertEquals(13, $entity->data[0][5]); // notifications COUNT
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(2, count($entity->data));
        $this->assertEquals(140, $entity->data[0][0]); // scope id
        $this->assertStringContainsString('Hohenzollerndamm', $entity->data[0][3]); //scope name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(3, count($entity->data));
    }
}
