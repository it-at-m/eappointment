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
        $availabilityUnits = count($entity->readOldAvailabilityList());
        $availabilityDelete->startProcessing();
        $this->assertEquals(771, $availabilityUnits);
     
        $availabilityDelete->startProcessing($commit = true);
        $availabilityUnits = count($entity->readOldAvailabilityList());
        $this->assertEquals(0, $availabilityUnits);
    }
}
