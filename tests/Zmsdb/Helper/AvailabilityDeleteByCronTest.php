<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Apikey as Query;
use \BO\Zmsentities\Apikey as Entity;
use \BO\Zmsdb\Helper\AvailabilityDeleteByCron;
use \BO\Zmsdb\Availability;

class AvailabilityDeleteByCronTest extends Base
{

    public function testConstructor()
    {
        $availabilityObject = new AvailabilityDeleteByCron();
        $this->assertInstanceOf(AvailabilityDeleteByCron::class, $availabilityObject);
    }

    public function testStartProcessingNoCommit()
    {
        $availabilityDelete = new AvailabilityDeleteByCron($verbose = true); // verbose
        $entity = new Availability();
        $now = new \DateTimeImmutable('2016-05-01 11:55');
        $datetime = $now->modify('- 4 weeks');
        $availabilityUnits = count($entity->readOldAvailabilityList($now, $datetime));
        $availabilityDelete->startProcessing($now, $datetime);
        $this->assertEquals(33, $availabilityUnits);
     
        $availabilityDelete->startProcessing($now, $datetime, $commit = true);
        $availabilityUnits = count($entity->readOldAvailabilityList($now, $datetime));
        $this->assertEquals(0, $availabilityUnits);
    }
}
