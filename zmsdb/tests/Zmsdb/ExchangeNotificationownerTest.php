<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeNotificationowner as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeNotificationownerTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(23, new DateTime('2016-04-01'), new DateTime('2016-04-30'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(15, count($entity->data)); // all organisations for the owner
        $this->assertEquals(23, $entity->data[0][0]); // owner id
        $this->assertEquals("Berlin", $entity->data[0][2]); // owner name
        $this->assertEquals("Friedrichshain-Kreuzberg", $entity->data[0][3]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[0][4]); // department name
        $this->assertEquals("Bürgeramt 1 (Kreuzberg), Yorckstr.  ", $entity->data[0][5]); // scope name
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(23, $entity->data[0][0]); // owner id
        $this->assertEquals('Berlin', $entity->data[0][3]); //owner name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(23);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(3, count($entity->data));
    }
}
