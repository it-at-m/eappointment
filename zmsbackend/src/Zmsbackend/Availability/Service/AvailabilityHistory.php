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

    private const SUMMARY_MAX_LENGTH = 4000;

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'openinghours' => 'Spontankunden',
        'appointment' => 'Terminkunden',
    ];

    /** @var array<string, string> monday-first like admin accordionTitle() */
    private const WEEKDAY_LABELS = [
        'monday' => 'Montag',
        'tuesday' => 'Dienstag',
        'wednesday' => 'Mittwoch',
        'thursday' => 'Donnerstag',
        'friday' => 'Freitag',
        'saturday' => 'Samstag',
        'sunday' => 'Sonntag',
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
     *     summary:string,
     *     changedAt:string,
     *     changedBy:string
     * }>
     */
    public function readListByScopeId(
        int $scopeId,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?int $availabilityId = null
    ): array {
        $parameters = [
            'scopeId' => $scopeId,
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
        ];
        $query = AvailabilityHistoryQuery::QUERY_SELECT_BY_SCOPE;
        if ($availabilityId !== null) {
            $query = AvailabilityHistoryQuery::QUERY_SELECT_BY_SCOPE_AND_AVAILABILITY;
            $parameters['availabilityId'] = $availabilityId;
        }

        $rows = $this->fetchAll($query, $parameters);

        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'id' => (int) $row['id'],
                'scopeId' => (int) $row['scopeId'],
                'availabilityId' => $row['availabilityId'] !== null ? (int) $row['availabilityId'] : null,
                'action' => (string) $row['action'],
                'summary' => (string) $row['summary'],
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
     * Full form snapshot for tech-admin history (labels mirror the day form).
     */
    public function buildSummary(Availability $availability): string
    {
        $startDate = $availability->getStartDateTime()->format('d.m.Y');
        $endDate = $availability->getEndDateTime()->format('d.m.Y');
        $startTime = $this->formatTime((string) $availability->startTime);
        $endTime = $this->formatTime((string) $availability->endTime);

        $description = trim((string) ($availability->description ?? ''));
        $typeLabel = self::TYPE_LABELS[$availability->type] ?? (string) $availability->type;
        $seriesLabel = $this->resolveSeriesLabel($availability);
        $weekdayList = $this->resolveWeekdayList($availability);
        $slotMinutes = (int) ($availability->slotTimeInMinutes ?? $availability->getSlotTimeInMinutes());
        $bookableFrom = $availability->bookable['startInDays'] ?? null;
        $bookableTo = $availability->bookable['endInDays'] ?? null;
        $intern = (int) ($availability->workstationCount['intern'] ?? 0);
        $public = (int) ($availability->workstationCount['public'] ?? 0);

        $lines = [
            'Anmerkung: ' . ($description !== '' ? $description : '–'),
            'Typ: ' . $typeLabel,
            'Serie: ' . $seriesLabel,
            'Wochentage: ' . ($weekdayList !== '' ? $weekdayList : '–'),
            'Terminabstand: ' . $slotMinutes . ' min',
            "Öffnungszeit: {$startDate} {$startTime} – {$endDate} {$endTime}",
            'Buchbar: von ' . $this->formatBookableDay($bookableFrom)
                . ' bis ' . $this->formatBookableDay($bookableTo) . ' Tage im voraus',
            "Terminarbeitsplätze: Insgesamt {$intern} / Internet {$public}",
        ];

        if ($availability->hasId()) {
            $lines[] = 'ID: ' . $availability->getId();
        }

        $summary = implode("\n", $lines);

        if (mb_strlen($summary) > self::SUMMARY_MAX_LENGTH) {
            return mb_substr($summary, 0, self::SUMMARY_MAX_LENGTH - 3) . '...';
        }

        return $summary;
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

    protected function resolveWeekdayList(Availability $availability): string
    {
        $weekdayLabels = [];
        foreach (self::WEEKDAY_LABELS as $key => $label) {
            if (!empty($availability->weekday[$key])) {
                $weekdayLabels[] = $label;
            }
        }

        return implode(', ', $weekdayLabels);
    }

    protected function formatBookableDay($value): string
    {
        if ($value === null || $value === '') {
            return 'Standort-Standard';
        }

        return (string) $value;
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

            $query = new AvailabilityHistoryQuery(\BO\Zmsbackend\Query\Base::INSERT);
            $query->addValues($query->reverseEntityMapping([
                'scope_id' => $scopeId,
                'availability_id' => $availability->hasId() ? (int) $availability->getId() : null,
                'action' => $action,
                'summary' => $this->buildSummary($availability),
                'changed_by' => $changedBy ?? $this->resolveChangedBy(),
            ]));

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
