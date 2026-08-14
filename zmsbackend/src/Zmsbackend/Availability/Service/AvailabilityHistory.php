<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Service;

use BO\Zmsbackend\Availability\Repository\AvailabilityHistory as AvailabilityHistoryQuery;
use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Availability;
use BO\Zmsentities\AvailabilityHistory as Entity;
use BO\Zmsentities\Collection\AvailabilityHistoryList as Collection;
use App;

class AvailabilityHistory extends \BO\Zmsbackend\Base
{
    public const ACTION_CREATED = Entity::ACTION_CREATED;
    public const ACTION_UPDATED = Entity::ACTION_UPDATED;
    public const ACTION_DELETED = Entity::ACTION_DELETED;
    public const ACTION_DLDB_SLOT_UPDATE = Entity::ACTION_DLDB_SLOT_UPDATE;

    public const DEFAULT_RETENTION_DAYS = 180;
    public const MAX_ROWS = 500;

    private const DESCRIPTION_MAX_LENGTH = 512;

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'openinghours' => 'Spontankunden',
        'appointment' => 'Terminkunden',
    ];

    /** @var array<int, string> */
    private const SERIES_AFTER_WEEKS = [
        1 => 'jede Woche',
        2 => 'alle 2 Wochen',
        3 => 'alle 3 Wochen',
    ];

    /** @var array<int, string> */
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

    public function readListByScopeId(
        int $scopeId,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?int $availabilityId = null,
        ?string $action = null
    ): Collection {
        $query = new AvailabilityHistoryQuery(\BO\Zmsbackend\Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionChangedAtRange($from, $to)
            ->addLimit(self::MAX_ROWS);

        if ($availabilityId !== null) {
            $query->addConditionAvailabilityId($availabilityId);
        }
        if ($action !== null) {
            $query->addConditionAction($action);
        }

        $collection = new Collection();
        foreach ($this->fetchList($query, new Entity()) as $entity) {
            if ($entity instanceof Entity) {
                $collection->addEntity($entity);
            }
        }

        return $collection;
    }

    public function deleteOlderThanDays(int $days): int
    {
        $days = max(1, $days);
        $now = \DateTimeImmutable::createFromInterface(App::$now ?? new \DateTimeImmutable('now'));
        $cutoff = $now->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        return (int) $this->fetchAffected(AvailabilityHistoryQuery::QUERY_DELETE_OLDER_THAN, [
            'cutoff' => $cutoff,
        ]);
    }

    public function buildSnapshot(Availability $availability): array
    {
        $startDate = $availability->getStartDateTime()->format('d.m.Y');
        $endDate = $availability->getEndDateTime()->format('d.m.Y');
        $startTime = $availability->getStartDateTime()->format('H:i');
        $endTime = $availability->getEndDateTime()->format('H:i');
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
            'weekday' => Entity::encodeWeekdayMask($availability),
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
}
