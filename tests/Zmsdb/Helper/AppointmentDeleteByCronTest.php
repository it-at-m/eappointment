<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\AppointmentDeleteByCron;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusArchived;

class AppointmentDeleteByCronTest extends Base
{

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 11:55');
        $availabilityObject = new AppointmentDeleteByCron(1, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $availabilityObject);
    }

    public function testStartProcessingBlocked()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = static::$now;
        $entity =(new Query)->readEntity(10029, '1c56', 0);
        $entity->status = 'finished';
        $entity->queue->callTime = 1460972400;
        $queryArchived->writeEntityFinished($entity, $now);

        $appointmentDelete = new AppointmentDeleteByCron(2, $now, false); // verbose
        $query = new Query();
        $appointmentDelete->startProcessing(false, false);
        $this->assertEquals(1, count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
     
        $appointmentDelete->startProcessing(true, false);
        $this->assertEquals(0, count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
    }

    public function testStartProcessingBlockedPickup()
    {
        $queryArchived = new ProcessStatusArchived();
        $now = static::$now;
        $query = new Query();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = $query->writeNewPickup($scope, $now);
        $process = $query->readEntity($process->id, $process->authKey, 0);
        $process->status = 'finished';
        $queryArchived->writeEntityFinished($process, $now);

        $appointmentDelete = new AppointmentDeleteByCron(2, $now, false); // verbose
        $query = new Query();

        $appointmentDelete->startProcessing(false, false);
        $this->assertEquals(1, count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
     
        /*
        $appointmentDelete->startProcessing(true, false);
        $appointmentUnits = count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, $limit, $offset));
        $this->assertEquals(0, $appointmentUnits);
        */
    }
}
