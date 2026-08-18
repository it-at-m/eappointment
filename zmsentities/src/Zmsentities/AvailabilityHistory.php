<?php

declare(strict_types=1);

namespace BO\Zmsentities;

class AvailabilityHistory extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = 'availabilityhistory.json';

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_DLDB_SLOT_UPDATE = 'dldb_slot_update';

    /** @var list<string> */
    public const ACTIONS = [
        self::ACTION_CREATED,
        self::ACTION_UPDATED,
        self::ACTION_DELETED,
        self::ACTION_DLDB_SLOT_UPDATE,
    ];

    /** @var array<string, int> */
    public const WEEKDAY_BITS = [
        'sunday' => 1,
        'monday' => 2,
        'tuesday' => 4,
        'wednesday' => 8,
        'thursday' => 16,
        'friday' => 32,
        'saturday' => 64,
    ];

    #[\Override]
    public function getDefaults(): array
    {
        $weekday = [];
        foreach (array_keys(self::WEEKDAY_BITS) as $name) {
            $weekday[$name] = 0;
        }

        return [
            'id' => null,
            'scopeId' => 0,
            'availabilityId' => null,
            'action' => self::ACTION_CREATED,
            'weekday' => $weekday,
            'startDate' => '',
            'endDate' => '',
            'everyXWeeks' => 0,
            'everyOtherWeek' => 0,
            'startTime' => '00:00:00',
            'appointmentStartTime' => '00:00:00',
            'endTime' => '00:00:00',
            'appointmentEndTime' => '00:00:00',
            'timeSlot' => '00:00:00',
            'workstationCount' => 0,
            'appointmentWorkstationCount' => 0,
            'comment' => null,
            'internetReduction' => 0,
            'multipleSlotsAllowed' => 0,
            'openFromDays' => 0,
            'openUntilDays' => 0,
            'version' => 1,
            'changedAt' => '',
            'changedBy' => '',
        ];
    }

    public static function encodeWeekdayMask(Availability $availability): int
    {
        $mask = 0;
        foreach (self::WEEKDAY_BITS as $weekday => $bit) {
            if (!empty($availability->weekday[$weekday])) {
                $mask |= $bit;
            }
        }

        return $mask;
    }

    /** @return array<string, int> */
    public static function decodeWeekdayMask(int $mask): array
    {
        $weekday = [];
        foreach (self::WEEKDAY_BITS as $name => $bit) {
            $weekday[$name] = ($mask & $bit) ? $bit : 0;
        }

        return $weekday;
    }
}
