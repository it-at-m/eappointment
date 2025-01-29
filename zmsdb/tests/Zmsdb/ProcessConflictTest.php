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
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-04-01 11:55");
        $endDate = new \DateTimeImmutable("2016-04-30 23:59");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())
            ->readConflictListByScopeAndTime($scope, $startDate, $endDate, $now, 0);
        $this->assertEquals(10, $conflictList->count());
    }

    public function testWithoutQueued()
    {
        $startDate = new \DateTimeImmutable("2016-04-01 08:55");
        $endDate = new \DateTimeImmutable("2016-04-30 23:59");
        $now = static::$now;
        $query = new \BO\Zmsdb\ProcessStatusQueued();
        $entity = (new \BO\Zmsentities\Process())->getExample();
        $entity->scope['id'] = 141;
        $entity->status = 'queued';
        $entity->id = 0;
        $entity->queue['number'] = 0;
        $process = $query->writeNewFromAdmin($entity, $now);

        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())
            ->readConflictListByScopeAndTime($scope, $startDate, $endDate, $now, 0);
        $this->assertEquals(10, $conflictList->count());
    }

    public function testOverbookedOnDay()
    {
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-04-12 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
    }

    /*
     * Test a single day availability without repeats but with conflicts out of availability start and enttime
     */

    public function testSingleDayOutOfAvailability()
    {
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-04-08 07:00");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(154, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())
            ->readConflictListByScopeAndTime($scope, $startDate, null, $now, 1)
            ->withoutDublicatedConflicts()
            ->setConflictAmendment();
        $this->assertEquals(8, $conflictList->count());
        $this->assertStringContainsString(
            'Der Vorgang (12437) befindet sich außerhalb der Öffnungszeit!',
            $conflictList->getFirst()->amendment
        );
        $this->assertFalse($conflictList->getFirst()->getFirstAppointment()->availability->hasId());
    }

    public function testSingleDayOverbookedSlots()
    {
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-04-25 10:00");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(154, 1, true);
        $conflictList = (new \BO\Zmsdb\Process())
            ->readConflictListByScopeAndTime($scope, $startDate, null, $now, 1)
            ->setConflictAmendment();
        $this->assertEquals(1, $conflictList->count());
        $this->assertStringContainsString(
            'Die Slots für diesen Zeitraum wurden überbucht',
            $conflictList->getFirst()->amendment
        );
        $this->assertTrue($conflictList->getFirst()->getFirstAppointment()->availability->hasId());
    }

    public function testEqual()
    {
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-05-13 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $availabilityList = (new \BO\Zmsdb\Availability())->readAvailabilityListByScope($scope, 1);
        $availabilityCopy = clone $availabilityList->withDateTime($startDate)->getFirst();
        $availabilityCopy->endTime = $availabilityCopy->getEndDateTime()->format('H:i');
        (new \BO\Zmsdb\Availability())->writeEntity($availabilityCopy);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
        $this->assertEquals("Konflikt: Zwei Öffnungszeiten sind gleich.\n" .
            "Bestehende Öffnungszeit:&thinsp;&thinsp;[30.01.2016 - 22.05.2016, 08:00 - 13:50]\n" .
            "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[30.01.2016 - 22.05.2016, 08:00 - 13:50]", $conflictList->getFirst()->getAmendment());
        $this->assertEquals('conflict', $conflictList->getFirst()->getStatus());
    }

    public function testOverLap()
    {
        $now = static::$now;
        $startDate = new \DateTimeImmutable("2016-05-13 11:55");
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 1, true);
        $availabilityList = (new \BO\Zmsdb\Availability())->readAvailabilityListByScope($scope, 1);
        $availabilityCopy = clone $availabilityList->withDateTime($startDate)->getFirst();
        $availabilityCopy->endTime = $availabilityCopy->getEndDateTime()->modify('+2 hour')->format('H:i');
        (new \BO\Zmsdb\Availability())->writeEntity($availabilityCopy);
        $conflictList = (new \BO\Zmsdb\Process())->readConflictListByScopeAndTime($scope, $startDate, null, $now, 0);
        $this->assertEquals(2, $conflictList->count());
        $this->assertEquals("Konflikt: Zwei Öffnungszeiten überschneiden sich.\n" .
            "Bestehende Öffnungszeit:&thinsp;&thinsp;[30.01.2016 - 22.05.2016, 08:00 - 15:50]\n" .
            "Neue Öffnungszeit:&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;[30.01.2016 - 22.05.2016, 08:00 - 13:50]", $conflictList->getFirst()->getAmendment());
        $this->assertEquals('conflict', $conflictList->getFirst()->getStatus());
    }
}
