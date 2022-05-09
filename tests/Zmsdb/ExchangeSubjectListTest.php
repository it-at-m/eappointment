<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Warehouse as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeSubjectListTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readSubjectsList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
    }
}
