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
        if (!$rows) {
            return;
        }

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
            'scope_id' => $scopeId,
            'availability_id' => $availabilityId,
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

    public function book(int $scopeId, string $startTime, int $processId, int $slotUnits): void
    {
        $start = new DateTimeImmutable($startTime);
        $end   = $start->add(new DateInterval('PT' . ($slotUnits * 5) . 'M'));

        $windowBefore = $this->fetchRow('
            SELECT
              SUM(status="free")      AS free_cnt,
              SUM(status="cancelled") AS cancelled_cnt,
              SUM(status="termin")    AS termin_cnt,
              COUNT(DISTINCT availability_id) AS availability_ids
            FROM gesamtkalender
            WHERE scope_id=:scope AND time>=:start AND time<:end
        ', [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]) ?? ['free_cnt' => 0,'cancelled_cnt' => 0,'termin_cnt' => 0,'availability_ids' => 0];

        $availabilityDetails = $this->fetchAll('
            SELECT DISTINCT
                   g.availability_id,
                   a.OeffnungszeitID,
                   a.Startdatum, a.Endedatum,
                   a.Anfangszeit, a.Terminanfangszeit,
                   a.Endzeit, a.Terminendzeit,
                   a.Timeslot,
                   a.Anzahlarbeitsplaetze,
                   a.Anzahlterminarbeitsplaetze
            FROM gesamtkalender g
            LEFT JOIN oeffnungszeit a ON a.OeffnungszeitID = g.availability_id
            WHERE g.scope_id=:scope AND g.time>=:start AND g.time<:end
        ', [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]);

        $recentCancelled = (int)$this->fetchValue('
            SELECT COUNT(*) FROM gesamtkalender
             WHERE scope_id=:scope AND time>=:start AND time<:end
               AND status="cancelled" AND updated_at > (NOW() - INTERVAL 2 MINUTE)
        ', [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]);

        \App::$log->info('calendar.book.attempt', [
            'scope_id'        => $scopeId,
            'process_id'      => $processId,
            'window'          => ['from' => $start->format('Y-m-d H:i:s'), 'until' => $end->format('Y-m-d H:i:s')],
            'slot_units'      => $slotUnits,
            'window_before'   => $windowBefore,
            'availability'    => $availabilityDetails,
            'recent_cancelled' => $recentCancelled,
        ]);

        $seat = $this->fetchValue(Calender::FIND_FREE_SEAT, [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
            'units' => $slotUnits,
        ]);

        if (!$seat) {
            \App::$log->warning('calendar.book.no_seat', [
                'scope_id'        => $scopeId,
                'process_id'      => $processId,
                'window'          => ['from' => $start->format('Y-m-d H:i:s'), 'until' => $end->format('Y-m-d H:i:s')],
                'slot_units'      => $slotUnits,
                'window_before'   => $windowBefore,
                'recent_cancelled' => $recentCancelled,
            ]);
            return;
        }

        try {
            $this->perform(Calender::BLOCK_SEAT_RANGE, [
                'pid'   => $processId,
                'units' => $slotUnits,
                'scope' => $scopeId,
                'seat'  => $seat,
                'start' => $start->format('Y-m-d H:i:s'),
                'end'   => $end  ->format('Y-m-d H:i:s'),
            ]);
        } catch (\PDOException $e) {
            \App::$log->critical('calendar.book.update_failed', [
                'scope_id'   => $scopeId,
                'process_id' => $processId,
                'seat'       => $seat,
                'error'      => $e->getMessage()
            ]);
            throw $e;
        }

        $windowAfter = $this->fetchRow('
            SELECT
              SUM(status="free")      AS free_cnt,
              SUM(status="cancelled") AS cancelled_cnt,
              SUM(status="termin")    AS termin_cnt
            FROM gesamtkalender
            WHERE scope_id=:scope AND time>=:start AND time<:end
        ', [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]) ?? ['free_cnt' => 0,'cancelled_cnt' => 0,'termin_cnt' => 0];

        $terminByPid = (int)$this->fetchValue('
            SELECT COUNT(*) FROM gesamtkalender
             WHERE scope_id=:scope AND time>=:start AND time<:end
               AND status="termin" AND process_id=:pid
        ', [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
            'pid'   => $processId,
        ]);

        \App::$log->info('calendar.book.result', [
            'scope_id'       => $scopeId,
            'process_id'     => $processId,
            'seat'           => $seat,
            'window_after'   => $windowAfter,
            'termin_by_pid'  => $terminByPid,
            'complete_chain' => ($terminByPid === $slotUnits),
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
