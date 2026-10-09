<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Tests\Availability\Service;

use BO\Zmsbackend\Availability\Service\Availability as AvailabilityService;
use BO\Zmsbackend\Availability\Service\AvailabilitySlotChange;
use BO\Zmsentities\Availability as Entity;

class AvailabilitySlotChangeTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const int SCOPE_ID = 65001;

    private const int BOOKED_PROCESS_ID = 991627;

    private const int CANCELLED_PROCESS_ID = 991628;

    public function testSplitsAtTheDayAfterTheFurthestBooking(): void
    {
        $spanning = $this->writeAvailability('2016-04-01', '2016-06-30', '08:00:00', '12:00:00', 60);
        $alreadyOver = $this->writeAvailability('2016-04-01', '2016-04-10', '08:00:00', '12:00:00', 60);
        $laterSeries = $this->writeAvailability('2016-05-01', '2016-06-01', '08:00:00', '12:00:00', 60);
        $openingHours = $this->writeAvailability('2016-04-01', '2016-06-30', '08:00:00', '12:00:00', 60, 'openinghours');
        $this->insertProcess(self::BOOKED_PROCESS_ID, '2016-04-15', 'confirmed');
        $this->insertProcess(self::CANCELLED_PROCESS_ID, '2016-05-20', 'deleted');

        (new AvailabilitySlotChange())->apply(self::SCOPE_ID, 90, static::$now);

        $kept = $this->readRow($spanning);
        $this->assertSame('2016-04-15', $kept['end_date']);
        $this->assertSame(60, (int) $kept['slot_minutes']);
        $this->assertSame('12:00:00', $kept['appointment_end_time']);

        $created = $this->readCopiedRow('2016-04-16');
        $this->assertNotNull($created);
        $this->assertSame('2016-06-30', $created['end_date']);
        $this->assertSame(90, (int) $created['slot_minutes']);
        $this->assertSame('11:00:00', $created['appointment_end_time']);
        $this->assertSame('00:00:00', $created['start_time']);
        $this->assertLessThanOrEqual('12:00:00', $created['appointment_end_time']);

        $untouched = $this->readRow($alreadyOver);
        $this->assertSame('2016-04-10', $untouched['end_date']);
        $this->assertSame(60, (int) $untouched['slot_minutes']);

        $retargeted = $this->readRow($laterSeries);
        $this->assertSame('2016-05-01', $retargeted['start_date']);
        $this->assertSame('2016-06-01', $retargeted['end_date']);
        $this->assertSame(90, (int) $retargeted['slot_minutes']);
        $this->assertSame('11:00:00', $retargeted['appointment_end_time']);

        $opening = $this->readRow($openingHours);
        $this->assertSame('2016-04-15', $opening['end_date']);
        $this->assertSame(60, (int) $opening['slot_minutes']);
        $this->assertSame('12:00:00', $opening['end_time']);
        $opened = $this->readCopiedOpening('2016-04-16');
        $this->assertNotNull($opened);
        $this->assertSame('11:00:00', $opened['end_time']);
        $this->assertSame('00:00:00', $opened['appointment_end_time']);
        $this->assertSame(90, (int) $opened['slot_minutes']);

        $history = $this->readHistory((int) $created['id']);
        $this->assertSame('dldb_slot_update', $history['action']);
        $this->assertSame('dldb', $history['changed_by']);
        $this->assertSame('Automatische Anpassung der Slotdauer', $history['comment']);
        $this->assertSame('01:30:00', $history['time_slot']);
    }

    public function testCutsTomorrowWhenNothingIsBooked(): void
    {
        $id = $this->writeAvailability('2016-04-01', '2016-05-01', '08:00:00', '12:00:00', 60);

        (new AvailabilitySlotChange())->apply(self::SCOPE_ID, 90, static::$now);

        $kept = $this->readRow($id);
        $this->assertSame('2016-04-01', $kept['end_date']);
        $this->assertSame(60, (int) $kept['slot_minutes']);
        $created = $this->readCopiedRow('2016-04-02');
        $this->assertNotNull($created);
        $this->assertSame(90, (int) $created['slot_minutes']);
        $this->assertSame('11:00:00', $created['appointment_end_time']);
    }

    public function testLeavesTheOpeningWhenTheNewSlotDoesNotFit(): void
    {
        $id = $this->writeAvailability('2016-04-01', '2016-06-30', '08:00:00', '09:00:00', 60);
        $this->insertProcess(self::BOOKED_PROCESS_ID, '2016-04-15', 'confirmed');

        (new AvailabilitySlotChange())->apply(self::SCOPE_ID, 90, static::$now);

        $row = $this->readRow($id);
        $this->assertSame('2016-06-30', $row['end_date']);
        $this->assertSame(60, (int) $row['slot_minutes']);
        $this->assertSame('09:00:00', $row['appointment_end_time']);
        $this->assertNull($this->readCopiedRow('2016-04-16'));
    }

    public function testDoesNothingWhenTheSlotAlreadyMatches(): void
    {
        $id = $this->writeAvailability('2016-04-01', '2016-06-30', '08:00:00', '12:00:00', 90);

        (new AvailabilitySlotChange())->apply(self::SCOPE_ID, 90, static::$now);

        $row = $this->readRow($id);
        $this->assertSame('2016-06-30', $row['end_date']);
        $this->assertSame(90, (int) $row['slot_minutes']);
        $this->assertNull($this->readCopiedRow('2016-04-02'));
    }

    #[\Override]
    public function tearDown(): void
    {
        $service = new AvailabilityService();
        $service->perform(
            'DELETE FROM `availability_history` WHERE `scope_id` = :scopeId',
            ['scopeId' => self::SCOPE_ID]
        );
        $service->perform(
            'DELETE FROM `oeffnungszeit` WHERE `scope_id` = :scopeId',
            ['scopeId' => self::SCOPE_ID]
        );
        $service->perform(
            'DELETE FROM `buerger` WHERE `BuergerID` IN (:booked, :cancelled)',
            ['booked' => self::BOOKED_PROCESS_ID, 'cancelled' => self::CANCELLED_PROCESS_ID]
        );
        parent::tearDown();
    }

    private function writeAvailability(
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        int $slotMinutes,
        string $type = 'appointment'
    ): int {
        $input = (new Entity())->createExample();
        $scope = $input['scope'];
        $scope['id'] = self::SCOPE_ID;
        $input['scope'] = $scope;
        $input['type'] = $type;
        $input['startTime'] = $startTime;
        $input['endTime'] = $endTime;
        $input['slotTimeInMinutes'] = $slotMinutes;
        $input['startDate'] = (new \DateTime($startDate . ' 00:00:00'))->getTimestamp();
        $input['endDate'] = (new \DateTime($endDate . ' 00:00:00'))->getTimestamp();
        $input['description'] = 'Slot change test';
        $weekday = $input['weekday'];
        foreach (array_keys($weekday) as $day) {
            $weekday[$day] = true;
        }
        $input['weekday'] = $weekday;

        $entity = (new AvailabilityService())->writeEntity($input);

        return (int) $entity->getId();
    }

    private function insertProcess(int $processId, string $date, string $status): void
    {
        (new AvailabilityService())->perform(
            'INSERT INTO `buerger`
                (`BuergerID`,`StandortID`,`Datum`,`Uhrzeit`,`Name`,`EMail`,`IPTimeStamp`,
                 `absagecode`,`bestaetigt`,`vorlaeufigeBuchung`,`status`)
             VALUES
                (:id, :scopeId, :date, \'09:00:00\', \'Muster Slot\',
                 \'muster.slot@mailinator.com\', 1460000000, :auth, 1, 0, :status)',
            [
                'id' => $processId,
                'scopeId' => self::SCOPE_ID,
                'date' => $date,
                'auth' => 'slot' . $processId,
                'status' => $status,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function readRow(int $id): array
    {
        $row = (new AvailabilityService())->fetchRow(
            'SELECT OeffnungszeitID AS id, start_date, end_date, start_time, end_time,
                    appointment_start_time, appointment_end_time,
                    TIME_TO_SEC(time_slot) DIV 60 AS slot_minutes
             FROM oeffnungszeit WHERE OeffnungszeitID = :id',
            ['id' => $id]
        );
        $this->assertIsArray($row);

        return $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readCopiedRow(string $startDate): ?array
    {
        $row = (new AvailabilityService())->fetchRow(
            'SELECT OeffnungszeitID AS id, start_date, end_date, start_time, end_time,
                    appointment_start_time, appointment_end_time,
                    TIME_TO_SEC(time_slot) DIV 60 AS slot_minutes
             FROM oeffnungszeit
             WHERE scope_id = :scopeId
               AND start_date = :startDate
               AND appointment_end_time <> \'00:00:00\'
             ORDER BY OeffnungszeitID DESC
             LIMIT 1',
            ['scopeId' => self::SCOPE_ID, 'startDate' => $startDate]
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readCopiedOpening(string $startDate): ?array
    {
        $row = (new AvailabilityService())->fetchRow(
            'SELECT OeffnungszeitID AS id, start_date, end_date, start_time, end_time,
                    appointment_start_time, appointment_end_time,
                    TIME_TO_SEC(time_slot) DIV 60 AS slot_minutes
             FROM oeffnungszeit
             WHERE scope_id = :scopeId
               AND start_date = :startDate
               AND appointment_start_time = \'00:00:00\'
               AND start_time <> \'00:00:00\'
             ORDER BY OeffnungszeitID DESC
             LIMIT 1',
            ['scopeId' => self::SCOPE_ID, 'startDate' => $startDate]
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function readHistory(int $availabilityId): array
    {
        $row = (new AvailabilityService())->fetchRow(
            'SELECT action, comment, changed_by, time_slot
             FROM availability_history
             WHERE availability_id = :id
             ORDER BY id DESC
             LIMIT 1',
            ['id' => $availabilityId]
        );
        $this->assertIsArray($row);

        return $row;
    }
}
