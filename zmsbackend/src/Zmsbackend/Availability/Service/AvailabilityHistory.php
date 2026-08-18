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

    private const COMMENT_MAX_LENGTH = 200;

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
        $comment = $availability->description ?? null;
        if (is_string($comment) && mb_strlen($comment) > self::COMMENT_MAX_LENGTH) {
            $comment = mb_substr($comment, 0, self::COMMENT_MAX_LENGTH - 3) . '...';
        }

        $intern = (int) ($availability->workstationCount['intern'] ?? 0);
        $public = (int) ($availability->workstationCount['public'] ?? 0);
        $slotMinutes = (int) ($availability->slotTimeInMinutes ?? $availability->getSlotTimeInMinutes());
        $isOpeningHours = $availability->type === 'openinghours';

        return [
            'start_date' => $availability->getStartDateTime()->format('Y-m-d'),
            'end_date' => $availability->getEndDateTime()->format('Y-m-d'),
            'every_x_weeks' => (int) ($availability->repeat['afterWeeks'] ?? 0),
            'every_other_week' => (int) ($availability->repeat['weekOfMonth'] ?? 0),
            'weekday' => Entity::encodeWeekdayMask($availability),
            'start_time' => $isOpeningHours ? $this->formatTimeValue($availability->startTime) : '00:00:00',
            'end_time' => $isOpeningHours ? $this->formatTimeValue($availability->endTime) : '00:00:00',
            'appointment_start_time' => $isOpeningHours
                ? '00:00:00'
                : $this->formatTimeValue($availability->startTime),
            'appointment_end_time' => $isOpeningHours
                ? '00:00:00'
                : $this->formatTimeValue($availability->endTime),
            'time_slot' => gmdate('H:i:s', max(0, $slotMinutes) * 60),
            'workstation_count' => 0,
            'appointment_workstation_count' => $intern,
            'comment' => $comment,
            'internet_reduction' => $intern - $public,
            'multiple_slots_allowed' => !empty($availability->multipleSlotsAllowed) ? 1 : 0,
            'open_from_days' => (int) ($availability->bookable['startInDays'] ?? 0),
            'open_until_days' => (int) ($availability->bookable['endInDays'] ?? 0),
            'version' => $availability->version !== null ? (int) $availability->version : 1,
        ];
    }

    protected function formatTimeValue(mixed $value): string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return '00:00:00';
        }

        $value = trim((string) $value);
        if (preg_match('/^\d{1,2}:\d{2}$/', $value) === 1) {
            return $value . ':00';
        }

        return $value;
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
