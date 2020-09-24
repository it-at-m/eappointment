<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessConflictTest extends Base
{
    public function testBasic()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $startDate = new \DateTimeImmutable("2016-04-01 11:55");
        $endDate = new \DateTimeImmutable("2016-04-30 23:59");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, $endDate, $now, 0);
        $this->assertEquals(10, $conflictList->count());
    }

    public function testWithoutQueued()
    {
        $startDate = new \DateTimeImmutable("2016-04-01 08:55");
        $endDate = new \DateTimeImmutable("2016-04-30 23:59");
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new \BO\Zmsdb\ProcessStatusQueued();
        $entity = (new \BO\Zmsentities\Process())->getExample();
        $entity->scope['id'] = 141;
        $entity->status = 'queued';
        $entity->id = 0;
        $entity->queue['number'] = 0;
        $process = $query->writeNewFromAdmin($entity, $now);

        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, $endDate, $now, 0);
        $this->assertEquals(10, $conflictList->count());
    }

    public function testOverbookedOnDay()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $startDate = new \DateTimeImmutable("2016-04-12 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
    }

    public function testEqual()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $startDate = new \DateTimeImmutable("2016-05-13 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $availabilityList = (new \BO\Zmsdb\Availability())->readAvailabilityListByScope($scope, 1);
        $availabilityCopy = clone $availabilityList->withDateTime($startDate)->getFirst();
        $availabilityCopy->endTime = $availabilityCopy->getEndDateTime()->format('H:i');
        (new \BO\Zmsdb\Availability())->writeEntity($availabilityCopy);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
        $this->assertEquals('Zwei Öffnungszeiten sind gleich.', $conflictList->getFirst()->getAmendment());
        $this->assertEquals('conflict', $conflictList->getFirst()->getStatus());
    }

    public function testOverLap()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $startDate = new \DateTimeImmutable("2016-05-13 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $availabilityList = (new \BO\Zmsdb\Availability())->readAvailabilityListByScope($scope, 1);
        $availabilityCopy = clone $availabilityList->withDateTime($startDate)->getFirst();
        $availabilityCopy->endTime = $availabilityCopy->getEndDateTime()->modify('+2 hour')->format('H:i');
        (new \BO\Zmsdb\Availability())->writeEntity($availabilityCopy);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
        $this->assertEquals('Zwei Öffnungszeiten überschneiden sich.', $conflictList->getFirst()->getAmendment());
        $this->assertEquals('conflict', $conflictList->getFirst()->getStatus());
    }
}
