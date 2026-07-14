<?php

namespace BO\Zmsbackend\Tests\Exchange\Service;

use \BO\Zmsbackend\Exchange\Service\ExchangeAvailabilityreview as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeAvailabilityreviewTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(45, count($entity->data));
        $this->assertEquals(101, $entity->data[0][2]); // Bürgeramt Pankow
        $this->assertEquals('2016-04-05', $entity->data[0][3]); // Startdatum
        $this->assertEquals('2030-12-31', $entity->data[0][4]); // Enddatum
        $this->assertEquals('00:12:00', $entity->data[0][16]); // Slotlänge
        $this->assertEquals('56', $entity->data[0][21]); // Buchbar bis Tage voraus
    }
}
