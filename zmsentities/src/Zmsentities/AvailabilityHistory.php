<?php

declare(strict_types=1);

namespace BO\Zmsentities;

/**
 * Opening-hours change history row for tech-admin audit (ZMSKVR-1249).
 *
 * @property int|null $id
 * @property int $scopeId
 * @property int|null $availabilityId
 * @property string $action
 * @property array $weekday
 * @property string $series
 * @property string $validFrom
 * @property string $validTo
 * @property string $timeRange
 * @property string $type
 * @property string $slotTime
 * @property string $workstations
 * @property string $bookable
 * @property string $description
 * @property string $changedAt
 * @property string $changedBy
 */
class AvailabilityHistory extends Schema\Entity
{
    public const PRIMARY = 'id';

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

    /**
     * Same bit codes as availability.weekday / Availability repository mapping.
     *
     * @var array<string, int>
     */
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
            'series' => '',
            'validFrom' => '',
            'validTo' => '',
            'timeRange' => '',
            'type' => '',
            'slotTime' => '',
            'workstations' => '',
            'bookable' => '',
            'description' => '',
            'changedAt' => '',
            'changedBy' => '',
        ];
    }

    /**
     * Encode availability.weekday flags to the INT bit matrix stored in availability_history.weekday.
     */
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

    /**
     * Decode INT bit matrix to availability-style weekday map.
     *
     * @return array<string, int>
     */
    public static function decodeWeekdayMask(int $mask): array
    {
        $weekday = [];
        foreach (self::WEEKDAY_BITS as $name => $bit) {
            $weekday[$name] = ($mask & $bit) ? $bit : 0;
        }

        return $weekday;
    }
}
