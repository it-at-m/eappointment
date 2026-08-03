<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Service;

use BO\Zmsbackend\Availability\Repository\AvailabilityHistory as AvailabilityHistoryQuery;
use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Availability;
use App;

/**
 * Write opening-hours change history for tech-admin audit (ZMSKVR-1249).
 */
class AvailabilityHistory extends \BO\Zmsbackend\Base
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_DLDB_SLOT_UPDATE = 'dldb_slot_update';

    /** Default retention window for history rows (ZMSKVR-1249). */
    public const DEFAULT_RETENTION_DAYS = 180;

    private const DESCRIPTION_MAX_LENGTH = 512;

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'openinghours' => 'Spontankunden',
        'appointment' => 'Terminkunden',
    ];

    /** @var array<int, string> afterWeeks series labels (admin availabilitySeries) */
    private const SERIES_AFTER_WEEKS = [
        1 => 'jede Woche',
        2 => 'alle 2 Wochen',
        3 => 'alle 3 Wochen',
    ];

    /** @var array<int, string> weekOfMonth series labels */
    private const SERIES_WEEK_OF_MONTH = [
        1 => 'jede 1. Woche im Monat',
        2 => 'jede 2. Woche im Monat',
        3 => 'jede 3. Woche im Monat',
        4 => 'jede 4. Woche im Monat',
        5 => 'jede letzte Woche im Monat',
    ];

    public function writeCreated(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_CREATED, $availability, $changedBy);
    }

    public function writeUpdated(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_UPDATED, $availability, $changedBy);
    }

    public function writeDeleted(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_DELETED, $availability, $changedBy);
    }

    public function writeDldbSlotUpdate(Availability $availability): bool
    {
        return $this->write(self::ACTION_DLDB_SLOT_UPDATE, $availability, 'dldb');
    }

    /**
     * Newest-first history for a scope within [from, to] (inclusive timestamps).
     *
     * @return list<array{
     *     id:int,
     *     scopeId:int,
     *     availabilityId:?int,
     *     action:string,
     *     weekday:array<string, int>,
     *     series:string,
     *     validFrom:string,
     *     validTo:string,
     *     timeRange:string,
     *     type:string,
     *     slotTime:string,
     *     workstations:string,
     *     bookable:string,
     *     description:string,
     *     changedAt:string,
     *     changedBy:string
     * }>
     */
    public function readListByScopeId(
        int $scopeId,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?int $availabilityId = null,
        ?string $action = null
    ): array {
        $parameters = [
            'scopeId' => $scopeId,
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
        ];

        $query = AvailabilityHistoryQuery::QUERY_SELECT_COLUMNS
            . ' WHERE scope_id = :scopeId'
            . ' AND changed_at >= :from'
            . ' AND changed_at <= :to';

        if ($availabilityId !== null) {
            $query .= ' AND availability_id = :availabilityId';
            $parameters['availabilityId'] = $availabilityId;
        }
        if ($action !== null) {
            $query .= ' AND action = :action';
            $parameters['action'] = $action;
        }

        $query .= ' ORDER BY changed_at DESC, id DESC';

        $rows = $this->fetchAll($query, $parameters);

        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'id' => (int) $row['id'],
                'scopeId' => (int) $row['scopeId'],
                'availabilityId' => $row['availabilityId'] !== null ? (int) $row['availabilityId'] : null,
                'action' => (string) $row['action'],
                'weekday' => $this->decodeWeekdayMask((int) $row['weekday']),
                'series' => (string) $row['series'],
                'validFrom' => (string) $row['validFrom'],
                'validTo' => (string) $row['validTo'],
                'timeRange' => (string) $row['timeRange'],
                'type' => (string) $row['type'],
                'slotTime' => (string) $row['slotTime'],
                'workstations' => (string) $row['workstations'],
                'bookable' => (string) $row['bookable'],
                'description' => (string) $row['description'],
                'changedAt' => (string) $row['changedAt'],
                'changedBy' => (string) $row['changedBy'],
            ];
        }

        return $list;
    }

    /**
     * Delete history rows older than the given number of days.
     *
     * @return int number of deleted rows
     */
    public function deleteOlderThanDays(int $days): int
    {
        $days = max(1, $days);
        $now = \DateTimeImmutable::createFromInterface(App::$now ?? new \DateTimeImmutable('now'));
        $cutoff = $now->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        return (int) $this->fetchAffected(AvailabilityHistoryQuery::QUERY_DELETE_OLDER_THAN, [
            'cutoff' => $cutoff,
        ]);
    }

    /**
     * Day-table snapshot values for history persistence.
     *
     * @return array{
     *     weekday:int,
     *     series:string,
     *     valid_from:string,
     *     valid_to:string,
     *     time_range:string,
     *     type:string,
     *     slot_time:string,
     *     workstations:string,
     *     bookable:string,
     *     description:string
     * }
     */
    public function buildSnapshot(Availability $availability): array
    {
        $startDate = $availability->getStartDateTime()->format('d.m.Y');
        $endDate = $availability->getEndDateTime()->format('d.m.Y');
        $startTime = $this->formatTime((string) $availability->startTime);
        $endTime = $this->formatTime((string) $availability->endTime);
        $slotMinutes = (int) ($availability->slotTimeInMinutes ?? $availability->getSlotTimeInMinutes());
        $bookableFrom = $availability->bookable['startInDays'] ?? null;
        $bookableTo = $availability->bookable['endInDays'] ?? null;
        $intern = (int) ($availability->workstationCount['intern'] ?? 0);
        $public = (int) ($availability->workstationCount['public'] ?? 0);
        $description = trim((string) ($availability->description ?? ''));
        if (mb_strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
            $description = mb_substr($description, 0, self::DESCRIPTION_MAX_LENGTH - 3) . '...';
        }

        return [
            'weekday' => $this->encodeWeekdayMask($availability),
            'series' => $this->resolveSeriesLabel($availability),
            'valid_from' => $startDate,
            'valid_to' => $endDate,
            'time_range' => "{$startTime} - {$endTime}",
            'type' => self::TYPE_LABELS[$availability->type] ?? (string) $availability->type,
            'slot_time' => $slotMinutes . 'min',
            'workstations' => "{$intern}/{$public}",
            'bookable' => $this->formatBookableRange($bookableFrom, $bookableTo),
            'description' => $description,
        ];
    }

    protected function resolveSeriesLabel(Availability $availability): string
    {
        $afterWeeks = (int) ($availability->repeat['afterWeeks'] ?? 0);
        $weekOfMonth = (int) ($availability->repeat['weekOfMonth'] ?? 0);

        if ($afterWeeks > 0) {
            return self::SERIES_AFTER_WEEKS[$afterWeeks] ?? "alle {$afterWeeks} Wochen";
        }
        if ($weekOfMonth > 0) {
            return self::SERIES_WEEK_OF_MONTH[$weekOfMonth]
                ?? ($weekOfMonth >= 5
                    ? self::SERIES_WEEK_OF_MONTH[5]
                    : "jede {$weekOfMonth}. Woche im Monat");
        }

        return 'einmaliger Termin';
    }

    /**
     * Encode availability.weekday flags to the same INT bit matrix as availability.weekday.
     */
    protected function encodeWeekdayMask(Availability $availability): int
    {
        $mask = 0;
        foreach (AvailabilityHistoryQuery::WEEKDAY_BITS as $weekday => $bit) {
            if (!empty($availability->weekday[$weekday])) {
                $mask |= $bit;
            }
        }

        return $mask;
    }

    /**
     * Decode INT bit matrix to availability-style weekday map.
     *
     * @return array<string, int>
     */
    protected function decodeWeekdayMask(int $mask): array
    {
        $weekday = [];
        foreach (AvailabilityHistoryQuery::WEEKDAY_BITS as $name => $bit) {
            $weekday[$name] = ($mask & $bit) ? $bit : 0;
        }

        return $weekday;
    }

    protected function formatBookableRange($from, $to): string
    {
        $fromLabel = ($from === null || $from === '') ? '?' : (string) $from;
        $toLabel = ($to === null || $to === '') ? '?' : (string) $to;

        return "{$fromLabel}-{$toLabel}";
    }

    protected function write(string $action, Availability $availability, ?string $changedBy): bool
    {
        try {
            $scopeId = (int) ($availability->scope['id'] ?? 0);
            if ($scopeId < 1) {
                App::$log->warning('availability_history skipped: missing scope_id', [
                    'action' => $action,
                    'availability_id' => $availability->id ?? null,
                ]);
                return false;
            }

            $snapshot = $this->buildSnapshot($availability);
            $query = new AvailabilityHistoryQuery(\BO\Zmsbackend\Query\Base::INSERT);
            $query->addValues($query->reverseEntityMapping(array_merge($snapshot, [
                'scope_id' => $scopeId,
                'availability_id' => $availability->hasId() ? (int) $availability->getId() : null,
                'action' => $action,
                'changed_by' => $changedBy ?? $this->resolveChangedBy(),
            ])));

            return (bool) $this->writeItem($query);
        } catch (\Throwable $exception) {
            App::$log->error('availability_history write failed', [
                'action' => $action,
                'availability_id' => $availability->id ?? null,
                'exception' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    protected function resolveChangedBy(): string
    {
        try {
            if (User::hasLogin()) {
                $userId = User::readWorkstation()->getUseraccount()->id ?? null;
                if ($userId !== null && $userId !== '') {
                    return (string) $userId;
                }
            }
        } catch (\Throwable $exception) {
            App::$log->warning('availability_history actor resolution failed', [
                'exception' => $exception->getMessage(),
            ]);
        }

        return 'system';
    }

    protected function formatTime(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
        foreach ($formats as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $value);
            if ($parsed instanceof \DateTimeImmutable) {
                return $parsed->format('H:i');
            }
        }

        return $value;
    }
}
