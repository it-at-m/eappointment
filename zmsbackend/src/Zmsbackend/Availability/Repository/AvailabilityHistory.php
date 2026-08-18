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
            'startDate' => self::ALIAS . '.start_date',
            'endDate' => self::ALIAS . '.end_date',
            'everyXWeeks' => self::ALIAS . '.every_x_weeks',
            'everyOtherWeek' => self::ALIAS . '.every_other_week',
            'weekday' => self::ALIAS . '.weekday',
            'startTime' => self::ALIAS . '.start_time',
            'appointmentStartTime' => self::ALIAS . '.appointment_start_time',
            'endTime' => self::ALIAS . '.end_time',
            'appointmentEndTime' => self::ALIAS . '.appointment_end_time',
            'timeSlot' => self::ALIAS . '.time_slot',
            'workstationCount' => self::ALIAS . '.workstation_count',
            'appointmentWorkstationCount' => self::ALIAS . '.appointment_workstation_count',
            'comment' => self::ALIAS . '.comment',
            'internetReduction' => self::ALIAS . '.internet_reduction',
            'multipleSlotsAllowed' => self::ALIAS . '.multiple_slots_allowed',
            'openFromDays' => self::ALIAS . '.open_from_days',
            'openUntilDays' => self::ALIAS . '.open_until_days',
            'version' => self::ALIAS . '.version',
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
            'start_date' => (string) $row['start_date'],
            'end_date' => (string) $row['end_date'],
            'every_x_weeks' => (int) ($row['every_x_weeks'] ?? 0),
            'every_other_week' => (int) ($row['every_other_week'] ?? 0),
            'weekday' => (int) $row['weekday'],
            'start_time' => (string) ($row['start_time'] ?? '00:00:00'),
            'appointment_start_time' => (string) ($row['appointment_start_time'] ?? '00:00:00'),
            'end_time' => (string) ($row['end_time'] ?? '00:00:00'),
            'appointment_end_time' => (string) ($row['appointment_end_time'] ?? '00:00:00'),
            'time_slot' => (string) ($row['time_slot'] ?? '00:00:00'),
            'workstation_count' => (int) ($row['workstation_count'] ?? 0),
            'appointment_workstation_count' => (int) ($row['appointment_workstation_count'] ?? 0),
            'comment' => $row['comment'] ?? null,
            'internet_reduction' => (int) ($row['internet_reduction'] ?? 0),
            'multiple_slots_allowed' => (int) ($row['multiple_slots_allowed'] ?? 0),
            'open_from_days' => (int) ($row['open_from_days'] ?? 0),
            'open_until_days' => (int) ($row['open_until_days'] ?? 0),
            'version' => (int) ($row['version'] ?? 1),
            'changed_by' => $row['changed_by'],
        ];
    }

    #[\Override]
    public function postProcess($data): array
    {
        $intFields = [
            'id',
            'scopeId',
            'everyXWeeks',
            'everyOtherWeek',
            'workstationCount',
            'appointmentWorkstationCount',
            'internetReduction',
            'multipleSlotsAllowed',
            'openFromDays',
            'openUntilDays',
            'version',
        ];
        foreach ($intFields as $field) {
            $data[$this->getPrefixed($field)] = (int) $data[$this->getPrefixed($field)];
        }

        $availabilityId = $data[$this->getPrefixed('availabilityId')];
        $data[$this->getPrefixed('availabilityId')] = $availabilityId !== null ? (int) $availabilityId : null;
        $data[$this->getPrefixed('weekday')] = Entity::decodeWeekdayMask((int) $data[$this->getPrefixed('weekday')]);
        $data[$this->getPrefixed('startDate')] = $this->asDateString($data[$this->getPrefixed('startDate')]);
        $data[$this->getPrefixed('endDate')] = $this->asDateString($data[$this->getPrefixed('endDate')]);
        $data[$this->getPrefixed('startTime')] = $this->asTimeString($data[$this->getPrefixed('startTime')]);
        $data[$this->getPrefixed('appointmentStartTime')] = $this->asTimeString(
            $data[$this->getPrefixed('appointmentStartTime')]
        );
        $data[$this->getPrefixed('endTime')] = $this->asTimeString($data[$this->getPrefixed('endTime')]);
        $data[$this->getPrefixed('appointmentEndTime')] = $this->asTimeString(
            $data[$this->getPrefixed('appointmentEndTime')]
        );
        $data[$this->getPrefixed('timeSlot')] = $this->asTimeString($data[$this->getPrefixed('timeSlot')]);

        return $data;
    }

    private function asDateString(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $value = (string) $value;
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1) {
            return substr($value, 0, 10);
        }

        return $value;
    }

    private function asTimeString(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s');
        }

        $value = (string) $value;
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?/', $value, $matches) === 1) {
            return $matches[0];
        }

        return $value;
    }
}
