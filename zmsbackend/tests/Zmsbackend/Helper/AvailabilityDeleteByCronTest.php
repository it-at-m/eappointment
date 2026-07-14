<?php

namespace BO\Zmsbackend\Tests\Helper;

use \BO\Zmsbackend\Helper\AvailabilityDeleteByCron;
use \BO\Zmsbackend\Availability\Service\Availability;

class AvailabilityDeleteByCronTest extends \BO\Zmsbackend\Tests\Service\Base
{

    public function testConstructor()
    {
        $availabilityObject = new AvailabilityDeleteByCron();
        $this->assertInstanceOf(AvailabilityDeleteByCron::class, $availabilityObject);
    }

    public function testStartProcessingNoCommit()
    {
        $availabilityDelete = new AvailabilityDeleteByCron($verbose = false); // verbose
        $entity = new Availability();
        $now = new \DateTimeImmutable('2016-05-01 11:55');
        $datetime = $now->modify('- 4 weeks');
        $availabilityUnits = count($entity->readAvailabilityListBefore($datetime));
        $availabilityDelete->startProcessing($datetime, $commit = false);
        $this->assertEquals(33, $availabilityUnits);
     
        $availabilityDelete->startProcessing($datetime, $commit = true);
        $availabilityUnits = count($entity->readAvailabilityListBefore($datetime));
        $this->assertEquals(0, $availabilityUnits);
    }
}
