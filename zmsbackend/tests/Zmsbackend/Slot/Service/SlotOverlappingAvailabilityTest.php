<?php

namespace BO\Zmsbackend\Tests\Slot\Service;

use BO\Zmsbackend\Slot\Service\Slot;

class SlotOverlappingAvailabilityTest extends \BO\Zmsbackend\Tests\Service\Base
{
    const int SCOPE_ID = 141;

    // scope 141, monday 08:00 - 15:50, 10 minute slots, 2 workstations
    const int LONG_AVAILABILITY_ID = 94666;

    const int TIMESTAMP_0830 = 1464589800; // 2016-05-30 08:30:00 +02:00

    const int TIMESTAMP_0850 = 1464591000; // 2016-05-30 08:50:00 +02:00

    protected static function getSlotRow($availabilityId, $time, $intern = 2, $confirmed = 0, $slotTime = 15)
    {
        return [
            'slotID' => 0,
            'availabilityID' => $availabilityId,
            'time' => $time,
            'slotTimeInMinutes' => $slotTime,
            'intern' => $intern,
            'confirmed' => $confirmed,
        ];
    }

    /**
     * Availability 1: 08:00 - 09:00, availability 2: 08:15 - 08:45
     */
    protected static function getOverlappingSlotRows(array $confirmedShort = [], array $confirmedLong = [])
    {
        $rows = [];
        foreach (['08:00:00', '08:15:00', '08:30:00', '08:45:00'] as $time) {
            $rows[] = static::getSlotRow(1, $time, 2, $confirmedLong[$time] ?? 0);
        }
        foreach (['08:15:00', '08:30:00'] as $time) {
            $rows[] = static::getSlotRow(2, $time, 1, $confirmedShort[$time] ?? 0);
        }
        return $rows;
    }

    public function testShorterAvailabilityIsPreferred()
    {
        $availabilityId = Slot::selectPreferredAvailabilityId(static::getOverlappingSlotRows(), '08:15:00', 1);
        $this->assertSame(2, $availabilityId);
    }

    public function testTimeInsideSlotIsMatched()
    {
        $availabilityId = Slot::selectPreferredAvailabilityId(static::getOverlappingSlotRows(), '08:20:00', 1);
        $this->assertSame(2, $availabilityId);
    }

    public function testOnlyCoveringAvailabilityIsUsed()
    {
        $availabilityId = Slot::selectPreferredAvailabilityId(static::getOverlappingSlotRows(), '08:00:00', 1);
        $this->assertSame(1, $availabilityId);
    }

    public function testLongerAvailabilityIsUsedIfShorterIsFull()
    {
        $rows = static::getOverlappingSlotRows(['08:15:00' => 1]);
        $availabilityId = Slot::selectPreferredAvailabilityId($rows, '08:15:00', 1);
        $this->assertSame(1, $availabilityId);
    }

    public function testFollowingSlotsMustFitInOneAvailability()
    {
        // 08:30 + 2 slots ends at 09:00, the short availability ends at 08:45
        $availabilityId = Slot::selectPreferredAvailabilityId(static::getOverlappingSlotRows(), '08:30:00', 2);
        $this->assertSame(1, $availabilityId);
    }

    public function testFollowingSlotsNeedFreeCapacity()
    {
        $rows = static::getOverlappingSlotRows(['08:30:00' => 1]);
        $availabilityId = Slot::selectPreferredAvailabilityId($rows, '08:15:00', 2);
        $this->assertSame(1, $availabilityId);
    }

    public function testShorterAvailabilityIsUsedIfAllAreFull()
    {
        $rows = static::getOverlappingSlotRows(['08:15:00' => 1], ['08:15:00' => 2]);
        $availabilityId = Slot::selectPreferredAvailabilityId($rows, '08:15:00', 1);
        $this->assertSame(2, $availabilityId);
    }

    public function testCandidateListLimitsAvailabilities()
    {
        $availabilityId = Slot::selectPreferredAvailabilityId(
            static::getOverlappingSlotRows(),
            '08:15:00',
            [1 => 1]
        );
        $this->assertSame(1, $availabilityId);
    }

    public function testCandidateListUsesSlotCountPerAvailability()
    {
        // Short availability does not allow multiple slots, so it needs only one slot
        $availabilityId = Slot::selectPreferredAvailabilityId(
            static::getOverlappingSlotRows(),
            '08:30:00',
            [1 => 2, 2 => 1]
        );
        $this->assertSame(2, $availabilityId);
    }

    public function testEqualDurationUsesLowerId()
    {
        $rows = [
            static::getSlotRow(7, '08:00:00'),
            static::getSlotRow(5, '08:00:00'),
        ];
        $this->assertSame(5, Slot::selectPreferredAvailabilityId($rows, '08:00:00', 1));
    }

    public function testNoSlotReturnsNull()
    {
        $this->assertNull(Slot::selectPreferredAvailabilityId(static::getOverlappingSlotRows(), '10:00:00', 1));
        $this->assertNull(Slot::selectPreferredAvailabilityId([], '08:00:00', 1));
    }

    public function testBookingInOverlapIsMappedToShorterAvailability()
    {
        $shortAvailability = $this->writeShortAvailability();
        $process = $this->writeReservedProcess(static::TIMESTAMP_0830);

        $this->assertSame(
            [$process->getId() => (int) $shortAvailability->getId()],
            $this->readMappedAvailabilityIds($process)
        );
        $this->assertSame(1, $this->readSlotProcessCount($process->getId()));
    }

    public function testBookingUsesLongerAvailabilityIfShorterIsFull()
    {
        $shortAvailability = $this->writeShortAvailability();
        $first = $this->writeReservedProcess(static::TIMESTAMP_0830);
        $second = $this->writeReservedProcess(static::TIMESTAMP_0830);
        $third = $this->writeReservedProcess(static::TIMESTAMP_0830);

        $this->assertSame([(int) $shortAvailability->getId()], array_values($this->readMappedAvailabilityIds($first)));
        $this->assertSame([static::LONG_AVAILABILITY_ID], array_values($this->readMappedAvailabilityIds($second)));
        $this->assertSame([static::LONG_AVAILABILITY_ID], array_values($this->readMappedAvailabilityIds($third)));

        // 2 workstations in the long and 1 in the short availability, every booking is counted only once
        $this->expectException(\BO\Zmsbackend\Process\Exception\ProcessReserveFailed::class);
        $this->writeReservedProcess(static::TIMESTAMP_0830);
    }

    public function testFollowingSlotsFittingInShorterAvailability()
    {
        $shortAvailability = $this->writeShortAvailability(true);
        $process = $this->writeReservedProcess(static::TIMESTAMP_0830, 2);

        $mapped = $this->readMappedAvailabilityIds($process);
        $this->assertCount(2, $mapped);
        $this->assertSame([(int) $shortAvailability->getId()], array_values(array_unique($mapped)));
        foreach (array_keys($mapped) as $processId) {
            $this->assertSame(1, $this->readSlotProcessCount($processId));
        }
    }

    public function testFollowingSlotsLeavingShorterAvailabilityStayInOneAvailability()
    {
        $this->writeShortAvailability(true);
        // 08:50 + 2 slots ends at 09:10, the short availability ends at 09:00
        $process = $this->writeReservedProcess(static::TIMESTAMP_0850, 2);

        $mapped = $this->readMappedAvailabilityIds($process);
        $this->assertCount(2, $mapped);
        $this->assertSame([static::LONG_AVAILABILITY_ID], array_values(array_unique($mapped)));
        foreach (array_keys($mapped) as $processId) {
            $this->assertSame(1, $this->readSlotProcessCount($processId));
        }
    }

    public function testRebuildMappingKeepsOneAvailabilityPerProcess()
    {
        $this->writeShortAvailability(true);
        $processList = [
            $this->writeReservedProcess(static::TIMESTAMP_0830, 2),
            $this->writeReservedProcess(static::TIMESTAMP_0830),
            $this->writeReservedProcess(static::TIMESTAMP_0850, 2),
        ];
        $processIds = array_keys($this->readMappedAvailabilityIdsForList($processList));

        $firstRebuild = $this->rebuildMapping($processIds, $processList);
        $this->assertSame($processIds, array_keys($firstRebuild));
        foreach ($processList as $process) {
            $mapped = $this->readMappedAvailabilityIds($process);
            $this->assertCount(1, array_unique($mapped), "Process $process is mapped to more than one availability");
        }
        foreach ($processIds as $processId) {
            $this->assertSame(1, $this->readSlotProcessCount($processId));
        }
        $this->assertSame([], (new Slot())->fetchAll('
            SELECT s.slotID
            FROM slot s
                INNER JOIN slot_process sp ON sp.slotID = s.slotID
            WHERE s.scopeID = :scopeID AND s.year = 2016 AND s.month = 5 AND s.day = 30
            GROUP BY s.slotID
            HAVING COUNT(sp.processID) > MAX(s.intern)
        ', ['scopeID' => static::SCOPE_ID]));

        // Rebuilding again gives the same mapping
        $this->assertSame($firstRebuild, $this->rebuildMapping($processIds, $processList));
    }

    protected function rebuildMapping(array $processIds, array $processList): array
    {
        $slotService = new Slot();
        foreach ($processIds as $processId) {
            $slotService->deleteSlotProcessMappingFor($processId);
        }
        $this->assertSame(count($processIds), $slotService->updateSlotProcessMapping(static::SCOPE_ID));
        return $this->readMappedAvailabilityIdsForList($processList);
    }

    protected function readMappedAvailabilityIdsForList(array $processList): array
    {
        $mapped = [];
        foreach ($processList as $process) {
            $mapped += $this->readMappedAvailabilityIds($process);
        }
        ksort($mapped);
        return $mapped;
    }

    public function testDeletedAdditionalAvailabilityShowsOverbooking()
    {
        $shortAvailability = $this->writeShortAvailability();
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity(static::SCOPE_ID, 1, true);
        $day = new \DateTimeImmutable('2016-05-30 00:00');
        $processService = new \BO\Zmsbackend\Process\Service\Process();
        $conflictsBefore = $processService->readConflictListByScopeAndTime($scope, $day, null, static::$now, 0);

        // 2 workstations in the long and 1 in the short availability
        for ($count = 0; $count < 3; $count++) {
            $this->writeReservedProcess(static::TIMESTAMP_0830);
        }
        $conflictList = $processService->readConflictListByScopeAndTime($scope, $day, null, static::$now, 0);
        $this->assertEquals($conflictsBefore->count(), $conflictList->count());

        (new \BO\Zmsbackend\Availability\Service\Availability())->deleteEntity($shortAvailability->getId());
        $conflictList = $processService->readConflictListByScopeAndTime($scope, $day, null, static::$now, 0);
        $this->assertEquals($conflictsBefore->count() + 1, $conflictList->count());
    }

    public function testWithoutOverlapMappingIsUnchanged()
    {
        $process = $this->writeReservedProcess(static::TIMESTAMP_0830);
        $this->assertSame(
            [$process->getId() => static::LONG_AVAILABILITY_ID],
            $this->readMappedAvailabilityIds($process)
        );
    }

    /**
     * Additional workstation on 2016-05-30 from 08:30 to 09:00 inside availability 94666
     */
    protected function writeShortAvailability(bool $multipleSlotsAllowed = false)
    {
        $availabilityQuery = new \BO\Zmsbackend\Availability\Service\Availability();
        if ($multipleSlotsAllowed) {
            $longAvailability = $availabilityQuery->readEntity(static::LONG_AVAILABILITY_ID, 0);
            $longAvailability->multipleSlotsAllowed = true;
            $availabilityQuery->updateEntity($longAvailability->id, $longAvailability);
        }
        $availability = $availabilityQuery->readEntity(static::LONG_AVAILABILITY_ID, 0);
        $availability->id = null;
        $availability->startDate = static::TIMESTAMP_0830;
        $availability->endDate = static::TIMESTAMP_0830;
        $availability->startTime = '08:30:00';
        $availability->endTime = '09:00:00';
        $availability->workstationCount['intern'] = 1;
        $availability->workstationCount['public'] = 1;
        $availability->workstationCount['callcenter'] = 1;
        $availability->multipleSlotsAllowed = $multipleSlotsAllowed;
        $availability->bookable['startInDays'] = 0;
        $availability->bookable['endInDays'] = 60;
        $availability = $availabilityQuery->writeEntity($availability, 2);
        (new Slot())->writeByAvailability($availability, static::$now);
        return $availability;
    }

    protected function writeReservedProcess(int $timestamp, int $slotCount = 1)
    {
        $input = \BO\Zmsbackend\Tests\Process\Service\ProcessTest::getTestProcessEntity();
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity(static::SCOPE_ID);
        $process = new \BO\Zmsentities\Process([
            'appointments' => [
                new \BO\Zmsentities\Appointment([
                    'date' => $timestamp,
                    'scope' => ['id' => static::SCOPE_ID],
                    'slotCount' => $slotCount,
                ])
            ],
            'scope' => $scope,
            'clients' => $input['clients'],
            'requests' => $input['requests'],
            'status' => 'free',
        ]);
        return (new \BO\Zmsbackend\Process\Service\ProcessStatusFree())->writeEntityReserved($process, static::$now);
    }

    /**
     * @return array<int, int> processId => availabilityId for the process and its following processes
     */
    protected function readMappedAvailabilityIds(\BO\Zmsentities\Process $process): array
    {
        $rows = (new Slot())->fetchAll('
            SELECT sp.processID, s.availabilityID
            FROM slot_process sp
                INNER JOIN slot s ON s.slotID = sp.slotID
                INNER JOIN buerger b ON b.BuergerID = sp.processID
            WHERE b.BuergerID = :processId OR b.istFolgeterminvon = :parentProcessId
            ORDER BY sp.processID
        ', ['processId' => $process->getId(), 'parentProcessId' => $process->getId()]);
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['processID']] = (int) $row['availabilityID'];
        }
        return $mapped;
    }

    protected function readSlotProcessCount($processId): int
    {
        $row = (new Slot())->fetchRow(
            'SELECT COUNT(*) AS mappings FROM slot_process WHERE processID = :processId',
            ['processId' => $processId]
        );
        return (int) $row['mappings'];
    }
}
