<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Repository;

use BO\Zmsentities\AvailabilityHistory as Entity;

class AvailabilityHistory extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    public const TABLE = 'availability_history';

    public const ALIAS = 'availabilityHistory';

    public const QUERY_DELETE_OLDER_THAN = '
        DELETE FROM availability_history
        WHERE changed_at < :cutoff
    ';

    /** @var list<string> */
    public const ALLOWED_ACTIONS = Entity::ACTIONS;

    protected $resolveLevel = 0;

    public function __construct($queryType, $prefix = '', $name = false, $resolveLevel = null)
    {
        parent::__construct($queryType, $prefix, $name, $resolveLevel);

        if ($queryType === self::SELECT) {
            $this->query->orderBy(self::ALIAS . '.changed_at', 'DESC');
            $this->query->orderBy(self::ALIAS . '.id', 'DESC');
        }
    }

    #[\Override]
    public function getEntityMapping()
    {
        return [
            'id' => self::ALIAS . '.id',
            'scopeId' => self::ALIAS . '.scope_id',
            'availabilityId' => self::ALIAS . '.availability_id',
            'action' => self::ALIAS . '.action',
            'weekday' => self::ALIAS . '.weekday',
            'series' => self::ALIAS . '.series',
            'validFrom' => self::ALIAS . '.valid_from',
            'validTo' => self::ALIAS . '.valid_to',
            'timeRange' => self::ALIAS . '.time_range',
            'type' => self::ALIAS . '.type',
            'slotTime' => self::ALIAS . '.slot_time',
            'workstations' => self::ALIAS . '.workstations',
            'bookable' => self::ALIAS . '.bookable',
            'description' => self::ALIAS . '.description',
            'changedAt' => self::ALIAS . '.changed_at',
            'changedBy' => self::ALIAS . '.changed_by',
        ];
    }

    public function addConditionScopeId(int $scopeId): self
    {
        $this->query->where(self::ALIAS . '.scope_id', '=', $scopeId);
        return $this;
    }

    public function addConditionChangedAtRange(\DateTimeInterface $from, \DateTimeInterface $to): self
    {
        $this->query->where(self::ALIAS . '.changed_at', '>=', $from->format('Y-m-d H:i:s'));
        $this->query->where(self::ALIAS . '.changed_at', '<=', $to->format('Y-m-d H:i:s'));
        return $this;
    }

    public function addConditionAvailabilityId(int $availabilityId): self
    {
        $this->query->where(self::ALIAS . '.availability_id', '=', $availabilityId);
        return $this;
    }

    public function addConditionAction(string $action): self
    {
        $this->query->where(self::ALIAS . '.action', '=', $action);
        return $this;
    }

    public function reverseEntityMapping(array $row): array
    {
        return [
            'scope_id' => (int) $row['scope_id'],
            'availability_id' => $row['availability_id'] !== null ? (int) $row['availability_id'] : null,
            'action' => $row['action'],
            'weekday' => (int) $row['weekday'],
            'series' => (string) $row['series'],
            'valid_from' => (string) $row['valid_from'],
            'valid_to' => (string) $row['valid_to'],
            'time_range' => (string) $row['time_range'],
            'type' => (string) $row['type'],
            'slot_time' => (string) $row['slot_time'],
            'workstations' => (string) $row['workstations'],
            'bookable' => (string) $row['bookable'],
            'description' => (string) $row['description'],
            'changed_by' => $row['changed_by'],
        ];
    }

    #[\Override]
    public function postProcess($data): array
    {
        $data[$this->getPrefixed('id')] = (int) $data[$this->getPrefixed('id')];
        $data[$this->getPrefixed('scopeId')] = (int) $data[$this->getPrefixed('scopeId')];
        $availabilityId = $data[$this->getPrefixed('availabilityId')];
        $data[$this->getPrefixed('availabilityId')] = $availabilityId !== null ? (int) $availabilityId : null;
        $data[$this->getPrefixed('weekday')] = Entity::decodeWeekdayMask((int) $data[$this->getPrefixed('weekday')]);

        return $data;
    }
}
