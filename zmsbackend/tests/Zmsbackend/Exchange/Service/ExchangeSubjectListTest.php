<?php

namespace BO\Zmsbackend\Tests\Exchange\Service;

use \BO\Zmsbackend\Warehouse\Service\Warehouse as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeSubjectListTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readSubjectsList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(15, count($entity->data));
    }
}
