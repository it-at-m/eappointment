<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Query\OverviewCalendar as Calender;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class OverviewCalendar extends Base
{
    private function fmtDateTime(\DateTimeInterface|string $dt): string
    {
        return ($dt instanceof \DateTimeInterface)
            ? $dt->format('Y-m-d H:i:s')
            : (string)$dt;
    }

    public function insert(
        int $scopeId,
        int $processId,
        string $status,
        \DateTimeInterface|string $startsAt,
        \DateTimeInterface|string $endsAt
    ): void {
        $this->perform(Calender::INSERT_ONE, [
            'scope_id' => $scopeId,
            'process_id' => $processId,
            'status' => $status,
            'starts_at' => $this->fmtDateTime($startsAt),
            'ends_at' => $this->fmtDateTime($endsAt),
        ]);
    }

    public function cancelByProcess(int $processId): bool
    {
        return (bool)$this->perform(\BO\Zmsdb\Query\OverviewCalendar::CANCEL_BY_PROCESS, [
            'process_id' => $processId,
        ]);
    }

    public function updateByProcess(
        int $processId,
        int $scopeId,
        \DateTimeInterface|string $startsAt,
        \DateTimeInterface|string $endsAt
    ): bool {
        return (bool)$this->perform(\BO\Zmsdb\Query\OverviewCalendar::UPDATE_BY_PROCESS, [
            'process_id' => $processId,
            'scope_id' => $scopeId,
            'starts_at' => $this->fmtDateTime($startsAt),
            'ends_at' => $this->fmtDateTime($endsAt),
        ]);
    }

    public function readMaxUpdated(array $scopeIds, string $from, string $until): ?string
    {
        if (empty($scopeIds)) {
            return null;
        }
        $in = implode(',', array_map('intval', $scopeIds));
        $sql = sprintf(Calender::SELECT_MAX_UPDATED, $in);

        $val = $this->fetchValue($sql, [
            'from' => $from,
            'until' => $until,
        ]);

        return $val ?: null;
    }

    public function readMaxUpdatedGlobal(array $scopeIds): ?string
    {
        if (empty($scopeIds)) {
            return null;
        }
        $in = implode(',', array_map('intval', $scopeIds));
        $sql = sprintf(Calender::SELECT_MAX_UPDATED_GLOBAL, $in);
        $val = $this->fetchValue($sql, []);
        return $val ?: null;
    }

    public function readRange(array $scopeIds, string $from, string $until): array
    {
        if (empty($scopeIds)) {
            return [];
        }
        $in = implode(',', array_map('intval', $scopeIds));
        $sql = sprintf(Calender::SELECT_RANGE, $in);

        return $this->fetchAll($sql, [
            'from' => $from,
            'until' => $until,
        ]);
    }

    public function readRangeUpdated(array $scopeIds, string $from, string $until, string $updatedAfter): array
    {
        if (empty($scopeIds)) {
            return [];
        }
        $in = implode(',', array_map('intval', $scopeIds));
        $sql = sprintf(Calender::SELECT_RANGE_UPDATED, $in);

        return $this->fetchAll($sql, [
            'from' => $from,
            'until' => $until,
            'updatedAfter' => $updatedAfter,
        ]);
    }

    public function readChangedProcessIdsSince(array $scopeIds, string $updatedAfter): array
    {
        if (empty($scopeIds)) {
            return [];
        }
        $in  = implode(',', array_map('intval', $scopeIds));
        $sql = sprintf(Calender::SELECT_CHANGED_PIDS_SINCE, $in);
        $rows = $this->fetchAll($sql, ['updatedAfter' => $updatedAfter]);
        return array_map(fn($r) => (int)$r['process_id'], $rows);
    }

    public function deleteOlderThan(\DateTimeInterface|string $threshold): bool
    {
        return (bool)$this->perform(Calender::DELETE_ALL_BEFORE_END, [
            'threshold' => $this->fmtDateTime($threshold),
        ]);
    }
}
