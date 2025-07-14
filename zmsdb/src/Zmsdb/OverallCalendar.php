<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Query\OverallCalendar as Calender;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class OverallCalendar extends Base
{
    public function insertSlotsBulk(array $rows): void
    {
        if (!$rows) return;

        $placeholders = rtrim(str_repeat('(?,?,?,?,?),', count($rows)), ',');
        $sql = sprintf(Query\OverallCalendar::UPSERT_MULTI, $placeholders);

        $params = [];
        foreach ($rows as $r) {
            $params[] = $r[0];
            $params[] = $r[1];
            $params[] = $r[2]->format('Y-m-d H:i:s');
            $params[] = (int)$r[3];
            $params[] = $r[4] ?? 'free';
        }
        $this->perform($sql, $params);
    }

    public function cancelAvailability(int $scopeId, int $availabilityId): void
    {
        $this->perform(Calender::CANCEL_AVAILABILITY, [
            'scope_id'=> $scopeId,
            'availability_id'=> $availabilityId,
        ]);
    }

    public function purgeMissingAvailabilityByScope(
        \DateTimeInterface $dateTime,
        int $scopeId
    ): bool {
        return (bool) $this->perform(
            Query\OverallCalendar::PURGE_MISSING_AVAIL_BY_SCOPE,
            [
                'dateString' => $dateTime->format('Y-m-d'),
                'scopeID'    => $scopeId,
            ]
        );
    }


    public function deleteOlderThan(DateTimeInterface $date): bool
    {
        return (bool) $this->perform(Calender::DELETE_ALL_BEFORE, [
            'threshold' => $date->format('Y-m-d 00:00:00'),
        ]);
    }

    public function book(
        int $scopeId,
        string $startTime,
        int $processId,
        int $slotUnits
    ): void {
        $start = new DateTimeImmutable($startTime);
        $end   = $start->add(new DateInterval('PT' . ($slotUnits * 5) . 'M'));

        $seat = $this->fetchValue(Calender::FIND_FREE_SEAT, [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
            'units' => $slotUnits,
        ]);

        if (!$seat) {
            error_log("Failed to book a seat for scope ID {$scopeId} from {$start->format('Y-m-d H:i:s')} to {$end->format('Y-m-d H:i:s')}. No free seats available.");
            return;
        }

        $this->perform(Calender::BLOCK_SEAT_RANGE, [
            'pid'   => $processId,
            'units' => $slotUnits,
            'scope' => $scopeId,
            'seat'  => $seat,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]);
    }

    public function unbook(int $scopeId, int $processId): void
    {
        $this->perform(Calender::UNBOOK_PROCESS, [
            'scope_id' => $scopeId,
            'process_id' => $processId,
        ]);
    }

    public function readSlots(
        array $scopeIds,
        string $from,
        string $until,
        ?string $updatedAfter = null
    ): array {
        if (empty($scopeIds)) {
            return [];
        }

        $in_list = implode(',', array_map('intval', $scopeIds));
        $until = (new \DateTime($until))->modify('+1 day')->format('Y-m-d');

        if ($updatedAfter === null) {
            $sql = sprintf(Calender::SELECT_RANGE, $in_list);
            $params = ['from' => $from, 'until' => $until];
        } else {
            $sql = sprintf(Calender::SELECT_RANGE_UPDATED, $in_list);
            $params = [
                'from'         => $from,
                'until'        => $until,
                'updatedAfter' => $updatedAfter
            ];
        }

        return $this->fetchAll($sql, $params);
    }
}
