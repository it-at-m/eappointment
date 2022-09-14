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
        $availabilityObject = new AppointmentDeleteByCron(0, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $availabilityObject);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $expired = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $query = new Query();
        $helper->startProcessing(false, false);
        $this->assertEquals(10, $helper->getCount()['confirmed']);
    }

    public function testStartProcessingExpiredExakt()
    {
        $now = new \DateTimeImmutable('2016-04-01 07:00');
        $expired = new \DateTimeImmutable('2016-04-01 07:00');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $query = new Query();
        $helper->startProcessing(false, false);
        $this->assertEquals(8, $helper->getCount()['confirmed']);
        $this->assertEquals(8, count($query->readExpiredProcessListByStatus($expired, 'confirmed')));
     
        $helper->startProcessing(true, false);
        $this->assertEquals(0, count($query->readExpiredProcessListByStatus($expired, 'confirmed')));
    }

    public function testStartProcessingBlockedPickup()
    {
        $now = static::$now;
        $query = new Query();
                
        $queryArchived = new ProcessStatusArchived();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = $query->writeNewPickup($scope, $now);
        $process = $query->readEntity($process->id, $process->authKey, 0);
        $process->status = 'finished';
        $queryArchived->writeEntityFinished($process, $now);

        $helper = new AppointmentDeleteByCron(1, $now, false); // verbose

        $helper->startProcessing(false, false);
        $this->assertEquals(3, count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
     
        $helper->startProcessing(true, false);
        $appointmentUnits = count($query->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0));
        $this->assertEquals(0, $appointmentUnits);
    }
}
